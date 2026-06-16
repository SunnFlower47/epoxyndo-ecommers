<?php

namespace App\Services\Shop;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Collection;

class CartService
{
    /**
     * Get user's cart items.
     */
    public function getCartItems(int|string|null $userId = null, ?string $sessionId = null): Collection
    {
        if ($userId) {
            return Cart::where('user_id', $userId)->with('product')->get();
        }

        if ($sessionId) {
            return Cart::where('session_id', $sessionId)->with('product')->get();
        }

        return collect();
    }

    /**
     * Add product to cart.
     */
    public function addToCart(int $productId, int $quantity, ?int $userId = null, ?string $sessionId = null): Cart
    {
        $query = Cart::where('product_id', $productId);

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId);
        }

        $cartItem = $query->first();

        if ($cartItem) {
            $cartItem->increment('qty', $quantity);
            return $cartItem;
        }

        return Cart::create([
            'user_id' => $userId,
            'session_id' => $userId ? null : $sessionId,
            'product_id' => $productId,
            'qty' => $quantity,
        ]);
    }

    /**
     * Update quantity of a cart item.
     */
    public function updateQuantity(int $cartId, int $quantity): bool
    {
        $cartItem = Cart::find($cartId);

        if ($cartItem) {
            return $cartItem->update(['qty' => $quantity]);
        }

        return false;
    }

    /**
     * Remove item from cart.
     */
    public function removeItem(int $cartId): bool
    {
        return (bool) Cart::destroy($cartId);
    }

    /**
     * Merge guest cart with user cart after login.
     */
    public function mergeCarts(int $userId, string $sessionId): void
    {
        $guestCartItems = Cart::where('session_id', $sessionId)->get();

        foreach ($guestCartItems as $guestItem) {
            $userItem = Cart::where('user_id', $userId)
                ->where('product_id', $guestItem->product_id)
                ->first();

            if ($userItem) {
                $userItem->increment('qty', $guestItem->qty);
                $guestItem->delete();
            } else {
                $guestItem->update([
                    'user_id' => $userId,
                    'session_id' => null,
                ]);
            }
        }
    }
}
