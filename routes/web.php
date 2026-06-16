<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

Route::get('/dev/clear-cache', function() {
    Artisan::call('optimize:clear');
    return redirect('/')->with('success', 'Cache cleared successfully!');
});

Route::get('/', function () {
    $disk = config('filament.default_filesystem_disk', 'public');
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
            if ($product->primaryImage && $product->primaryImage->image_path) {
                $product->primary_image_url = $isS3
                    ? Storage::disk($disk)->temporaryUrl($product->primaryImage->image_path, now()->addMinutes(60))
                    : Storage::disk($disk)->url($product->primaryImage->image_path);
            }
            return $product;
        });

    return Inertia::render('welcome', [
        'banners' => $banners,
        'products' => $products,
    ]);
})->name('home');

Route::get('/products', function (Illuminate\Http\Request $request) {
    $disk = config('filament.default_filesystem_disk', 'public');
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
        if ($product->primaryImage && $product->primaryImage->image_path) {
            $product->primary_image_url = $isS3
                ? Storage::disk($disk)->temporaryUrl($product->primaryImage->image_path, now()->addMinutes(60))
                : Storage::disk($disk)->url($product->primaryImage->image_path);
        }
        return $product;
    });

    return Inertia::render('products/index', [
        'products' => $products,
        'currentCategory' => $request->input('category'),
        'searchQuery' => $request->input('q'),
    ]);
});

// OAuth Routes
Route::get('/auth/google', [\App\Http\Controllers\Auth\SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\SocialAuthController::class, 'handleGoogleCallback']);

// Checkout Routes
Route::get('/checkout', [\App\Http\Controllers\Shop\CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout', [\App\Http\Controllers\Shop\CheckoutController::class, 'process'])->name('checkout.process');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

