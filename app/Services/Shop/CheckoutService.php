<?php

namespace App\Services\Shop;

use App\Models\User;
use App\Models\Order;
use App\Models\Coupon;
use App\Services\Order\OrderService;
use App\Services\Shipping\BiteshipService;
use App\Services\Payment\MidtransService;
use Illuminate\Support\Facades\DB;
use Exception;

class CheckoutService
{
    public function __construct(
        protected CartService $cartService,
        protected CouponService $couponService,
        protected OrderService $orderService
    ) {}

    /**
     * Calculate order totals before placing it.
     */
    public function calculateTotals(array $items, ?string $couponCode = null, int $shippingCost = 0, float $taxRate = 0.12): array
    {
        $subtotal = 0;
        foreach ($items as $item) {
            $price = $item['product']['sale_price'] ?? $item['product']['price'];
            $subtotal += $price * $item['qty'];
        }

        $discount = 0;
        $coupon = null;
        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();
            if ($coupon && $this->couponService->isValid($coupon, $subtotal)) {
                $discount = $this->couponService->calculateDiscount($coupon, $subtotal);
            }
        }

        $taxableAmount = max(0, $subtotal - $discount);
        $tax = round($taxableAmount * $taxRate);
        $total = $taxableAmount + $shippingCost + $tax;

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping_cost' => $shippingCost,
            'tax' => $tax,
            'total' => $total,
            'coupon_id' => $coupon?->id,
        ];
    }

    /**
     * Process checkout and place an order.
     */
    public function checkout(User $user, array $shippingAddress, string $courier, string $service, ?string $couponCode = null): Order
    {
        return DB::transaction(function () use ($user, $shippingAddress, $courier, $service, $couponCode) {
            // 1. Get cart items
            $cartItems = $this->cartService->getCartItems($user->id);
            if ($cartItems->isEmpty()) {
                throw new Exception("Cart is empty");
            }

            // 2. Estimate Shipping Cost (typically pre-saved or calculated from Biteship)
            $shippingCost = 15000; // Mock default shipping cost for skeleton

            // 3. Calculate Totals
            $totals = $this->calculateTotals($cartItems->toArray(), $couponCode, $shippingCost);

            // 4. Create Order
            $order = $this->orderService->createOrder(
                user: $user,
                items: $cartItems,
                totals: $totals,
                shippingAddress: $shippingAddress,
                courier: $courier,
                shippingService: $service
            );

            // 5. Clear Cart
            foreach ($cartItems as $item) {
                $item->delete();
            }

            // 6. If coupon is used, increment use count
            if ($totals['coupon_id']) {
                $coupon = Coupon::find($totals['coupon_id']);
                if ($coupon) {
                    $coupon->increment('used_count');
                }
            }

            return $order;
        });
    }
}
