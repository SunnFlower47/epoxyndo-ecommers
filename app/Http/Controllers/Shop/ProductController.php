<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display catalog of products.
     */
    public function index(Request $request): Response
    {
        $disk = config('filesystems.default', 'public');
        $isS3 = config("filesystems.disks.{$disk}.driver") === 's3';
        
        $query = Product::with(['primaryImage', 'category'])->where('is_active', true);

        if ($request->has('category')) {
            $categoryName = $request->input('category');
            $query->whereHas('category', function($q) use ($categoryName) {
                $q->where('name', 'like', "%{$categoryName}%");
            });
        }

        if ($request->has('q') && !empty($request->input('q'))) {
            $searchTerm = $request->input('q');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhereHas('category', function($catQuery) use ($searchTerm) {
                      $catQuery->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        $products = $query->paginate(16)->through(function($product) use ($disk, $isS3) {
            $product->append(['has_discount', 'final_price']);
            if ($product->primaryImage && $product->primaryImage->image_url) {
                $product->primary_image_url = $isS3
                    ? Storage::disk($disk)->temporaryUrl($product->primaryImage->image_url, now()->addMinutes(60))
                    : Storage::disk($disk)->url($product->primaryImage->image_url);
            }
            return $product;
        });

        return Inertia::render('products/index', [
            'products' => $products,
            'currentCategory' => $request->input('category'),
            'searchQuery' => $request->input('q'),
        ]);
    }

    /**
     * Display product detail.
     */
    public function show(string $slug): Response
    {
        $disk = config('filesystems.default', 'public');
        $isS3 = config("filesystems.disks.{$disk}.driver") === 's3';

        $product = Product::with(['images', 'category'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $product->append(['has_discount', 'final_price']);

        // Increment view_count safely (max 1 count per 24 hours per IP for this product)
        $ip = request()->ip();
        $cacheKey = 'product_view_' . $product->id . '_' . md5($ip);
        if (!\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            // Using DB raw to prevent touching updated_at if not needed, or simply increment
            $product->increment('view_count');
            \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addHours(24));
        }

        // Process images
        if ($product->images) {
            $product->images = $product->images->map(function($image) use ($disk, $isS3) {
                $image->image_url = $isS3
                    ? Storage::disk($disk)->temporaryUrl($image->image_url, now()->addMinutes(60))
                    : Storage::disk($disk)->url($image->image_url);
                return $image;
            });
        }

        // Get related products
        $relatedProducts = Product::with(['primaryImage', 'category'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->take(4)
            ->get()
            ->map(function($prod) use ($disk, $isS3) {
                $prod->append(['has_discount', 'final_price']);
                if ($prod->primaryImage && $prod->primaryImage->image_url) {
                    $prod->primary_image_url = $isS3
                        ? Storage::disk($disk)->temporaryUrl($prod->primaryImage->image_url, now()->addMinutes(60))
                        : Storage::disk($disk)->url($prod->primaryImage->image_url);
                }
                return $prod;
            });

        return Inertia::render('products/show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
