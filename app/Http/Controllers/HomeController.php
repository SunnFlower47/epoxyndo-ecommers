<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banner;
use App\Models\Product;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function index()
    {
        $disk = config('filesystems.default', 'public');
        $isS3 = config("filesystems.disks.{$disk}.driver") === 's3';

        $banners = Banner::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function($banner) use ($disk, $isS3) {
                if ($banner->image_url) {
                    $banner->image_url = $isS3 
                        ? Storage::disk($disk)->temporaryUrl($banner->image_url, now()->addMinutes(60))
                        : Storage::disk($disk)->url($banner->image_url);
                }
                return $banner;
            });

        $products = Product::with(['primaryImage', 'category'])
            ->where('is_active', true)
            ->latest()
            ->take(12)
            ->get()
            ->map(function($product) use ($disk, $isS3) {
                $product->append(['has_discount', 'final_price']);
                if ($product->primaryImage && $product->primaryImage->image_url) {
                    $product->primary_image_url = $isS3
                        ? Storage::disk($disk)->temporaryUrl($product->primaryImage->image_url, now()->addMinutes(60))
                        : Storage::disk($disk)->url($product->primaryImage->image_url);
                }
                return $product;
            });

        return Inertia::render('welcome', [
            'banners' => $banners,
            'products' => $products,
        ]);
    }
}
