<?php

namespace App\Services\Shop;

use App\Models\Wishlist;
use App\Models\User;
use Illuminate\Support\Collection;

class WishlistService
{
    /**
     * Get user's wishlist products.
     */
    public function getWishlist(User $user): Collection
    {
        return Wishlist::where('user_id', $user->id)
            ->with('product')
            ->get();
    }

    /**
     * Add product to wishlist.
     */
    public function add(User $user, int $productId): Wishlist
    {
        return Wishlist::firstOrCreate([
            'user_id' => $user->id,
            'product_id' => $productId,
        ]);
    }

    /**
     * Remove product from wishlist.
     */
    public function remove(User $user, int $productId): bool
    {
        return (bool) Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->delete();
    }

    /**
     * Check if product is in user's wishlist.
     */
    public function isWishlisted(User $user, int $productId): bool
    {
        return Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->exists();
    }
}
