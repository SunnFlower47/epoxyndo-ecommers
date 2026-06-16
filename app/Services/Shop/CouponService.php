<?php

namespace App\Services\Shop;

use App\Models\Coupon;
use Carbon\Carbon;

class CouponService
{
    /**
     * Check if a coupon is valid.
     */
    public function isValid(Coupon $coupon, float $purchaseAmount): bool
    {
        if (!$coupon->is_active) {
            return false;
        }

        $now = Carbon::now();

        if ($coupon->valid_from && $now->lt($coupon->valid_from)) {
            return false;
        }

        if ($coupon->valid_until && $now->gt($coupon->valid_until)) {
            return false;
        }

        if ($coupon->max_uses && $coupon->used_count >= $coupon->max_uses) {
            return false;
        }

        if ($purchaseAmount < $coupon->min_purchase) {
            return false;
        }

        return true;
    }

    /**
     * Calculate discount amount.
     */
    public function calculateDiscount(Coupon $coupon, float $purchaseAmount): float
    {
        if ($coupon->type === 'percent') {
            return round($purchaseAmount * ($coupon->value / 100));
        }

        // Fixed type
        return min($coupon->value, $purchaseAmount);
    }
}
