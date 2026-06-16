<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\Shop\CartService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class CartController extends Controller
{
    public function __construct(protected CartService $cartService)
    {
    }

    /**
     * Show the shopping cart.
     */
    public function index(Request $request): Response
    {
        $userId = auth()->id();
        $sessionId = $request->session()->getId();

        $cartItems = $this->cartService->getCartItems($userId, $sessionId);

        return Inertia::render('Shop/Cart', [
            'cartItems' => $cartItems,
        ]);
    }

    /**
     * Add an item to the cart.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $userId = auth()->id();
        $sessionId = $request->session()->getId();

        $this->cartService->addToCart(
            productId: $request->product_id,
            quantity: $request->qty,
            userId: $userId,
            sessionId: $sessionId
        );

        return back()->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }

    /**
     * Update the quantity of a cart item.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $this->cartService->updateQuantity($id, $request->qty);

        return back()->with('success', 'Jumlah produk berhasil diperbarui.');
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->cartService->removeItem($id);

        return back()->with('success', 'Produk berhasil dihapus dari keranjang.');
    }
}
