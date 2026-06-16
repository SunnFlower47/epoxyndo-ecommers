<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\Shop\WishlistService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class WishlistController extends Controller
{
    public function __construct(protected WishlistService $wishlistService)
    {
    }

    /**
     * Display user's wishlist page.
     */
    public function index(): Response
    {
        $wishlistItems = $this->wishlistService->getWishlist(auth()->user());

        return Inertia::render('Shop/Wishlist', [
            'wishlistItems' => $wishlistItems,
        ]);
    }

    /**
     * Add product to wishlist.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $this->wishlistService->add(auth()->user(), $request->product_id);

        return back()->with('success', 'Produk berhasil ditambahkan ke wishlist.');
    }

    /**
     * Remove product from wishlist.
     */
    public function destroy(int $productId): RedirectResponse
    {
        $this->wishlistService->remove(auth()->user(), $productId);

        return back()->with('success', 'Produk berhasil dihapus dari wishlist.');
    }
}
