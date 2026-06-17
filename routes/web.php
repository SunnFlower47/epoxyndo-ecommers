<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Shop\ProductController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\ReviewController;

Route::get('/dev/clear-cache', function() {
    Artisan::call('optimize:clear');
    return redirect('/')->with('success', 'Cache cleared successfully!');
});

// Home Page
Route::get('/', [HomeController::class, 'index'])->name('home');

// Product Routes
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');

// OAuth Routes
Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);

// Checkout Routes
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
Route::post('/api/shipping-rates', [CheckoutController::class, 'shippingRates']);
Route::post('/api/coupons/apply', [CheckoutController::class, 'applyCoupon']);
Route::post('/midtrans/callback', [CheckoutController::class, 'midtransCallback'])->name('midtrans.callback');

use App\Http\Controllers\ProfileController;

// Authenticated Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $user = auth()->user();
        $recentOrders = \App\Models\Order::where('user_id', $user->id)
                            ->latest()
                            ->take(5)
                            ->get();
        $totalOrders = \App\Models\Order::where('user_id', $user->id)->count();
        $activeOrders = \App\Models\Order::where('user_id', $user->id)
                            ->whereNotIn('status', ['completed', 'cancelled'])
                            ->count();

        return Inertia::render('dashboard', [
            'recentOrders' => $recentOrders,
            'totalOrders' => $totalOrders,
            'activeOrders' => $activeOrders,
        ]);
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');

    Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::post('/addresses/{address}/primary', [AddressController::class, 'setPrimary'])->name('addresses.primary');

    // Reviews API
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    // Notifications API
    Route::get('/api/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::post('/api/notifications/{id}/mark-as-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
});
