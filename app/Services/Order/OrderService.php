<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    /**
     * Create a new order.
     */
    public function createOrder(User $user, Collection $cartItems, array $totals, array $shippingAddress, string $courier, string $shippingService): Order
    {
        return DB::transaction(function () use ($user, $cartItems, $totals, $shippingAddress, $courier, $shippingService) {
            // 1. Create order record
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'shipping_cost' => $totals['shipping_cost'],
                'tax' => $totals['tax'],
                'total' => $totals['total'],
                'coupon_id' => $totals['coupon_id'] ?? null,
                'midtrans_order_id' => 'TRX-' . time() . '-' . $user->id,
                'expires_at' => now()->addHours(24),
                'notes' => $shippingAddress['notes'] ?? null,
            ]);

            // 2. Create order items
            foreach ($cartItems as $item) {
                $price = $item->product->sale_price ?? $item->product->price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'qty' => $item->qty,
                    'price_snapshot' => $price,
                    'product_name_snapshot' => $item->product->name,
                ]);
            }

            // 3. Create initial address and shipment records (stubbed)
            // $order->shipment()->create([...]);

            return $order;
        });
    }

    /**
     * Update order payment status (usually triggered by Webhook).
     */
    public function updatePaymentStatus(Order $order, string $status): void
    {
        DB::transaction(function () use ($order, $status) {
            if ($status === 'paid') {
                $order->update(['status' => 'processing']);

                // Deduct stock for all items
                foreach ($order->items as $item) {
                    $product = $item->product;
                    if ($product->stock < $item->qty) {
                        throw new Exception("Product {$product->name} is out of stock.");
                    }
                    $product->decrement('stock', $item->qty);
                }
            } elseif ($status === 'failed') {
                $order->update(['status' => 'cancelled']);
            }
        });
    }

    /**
     * Cancel expired orders and restore stock if they were already paid/deducted.
     */
    public function cancelOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            if ($order->status === 'cancelled') {
                return;
            }

            // Restore stock if it was already processing/shipped (though cancelling after ship is rare)
            if (in_array($order->status, ['processing', 'shipping'])) {
                foreach ($order->items as $item) {
                    $item->product->increment('stock', $item->qty);
                }
            }

            $order->update(['status' => 'cancelled']);
        });
    }
}
