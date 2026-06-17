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
            if ($status === 'paid' && $order->payment_status !== 'paid') {
                $order->update([
                    'status' => 'processing',
                    'payment_status' => 'paid',
                ]);

                // Send payment successful email
                try {
                    \Illuminate\Support\Facades\Mail::to($order->customer_email ?? $order->user?->email)
                        ->send(new \App\Mail\OrderPaidMail($order));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to send OrderPaidMail: ' . $e->getMessage());
                }
            } elseif ($status === 'failed' && $order->payment_status !== 'failed') {
                $order->update([
                    'status' => 'cancelled',
                    'payment_status' => 'failed',
                ]);
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

            // The Order model's booted() event will automatically handle stock restoration
            // if the order was previously paid and is now cancelled.
            $order->update(['status' => 'cancelled']);
        });
    }
}
