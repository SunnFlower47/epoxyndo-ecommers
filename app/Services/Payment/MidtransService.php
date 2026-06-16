<?php

namespace App\Services\Payment;

use App\Models\Order;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        // Set Midtrans configurations from config/env
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = config('services.midtrans.is_sanitized', true);
        Config::$is3ds = config('services.midtrans.is_3ds', true);
    }

    /**
     * Create Midtrans Snap Token & redirect URL for an order.
     */
    public function createSnapTransaction(Order $order): array
    {
        $params = [
            'transaction_details' => [
                'order_id' => $order->midtrans_order_id,
                'gross_amount' => (int) $order->total,
            ],
            'customer_details' => [
                'first_name' => $order->user->name,
                'email' => $order->user->email,
            ],
            // Items list can also be appended
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            $redirectUrl = Snap::getSnapUrl($params);

            return [
                'token' => $snapToken,
                'redirect_url' => $redirectUrl,
            ];
        } catch (\Exception $e) {
            throw new \Exception('Midtrans Transaction Creation Failed: ' . $e->getMessage());
        }
    }
}
