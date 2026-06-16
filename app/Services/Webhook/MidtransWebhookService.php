<?php

namespace App\Services\Webhook;

use App\Models\Order;
use App\Services\Order\OrderService;
use Illuminate\Support\Facades\Log;

class MidtransWebhookService
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Handle payment notification from Midtrans.
     */
    public function handleNotification(array $payload): bool
    {
        Log::info('Midtrans Webhook Payload:', $payload);

        $orderId = $payload['order_id'];
        $transactionStatus = $payload['transaction_status'];
        $fraudStatus = $payload['fraud_status'] ?? null;

        $order = Order::where('midtrans_order_id', $orderId)->first();

        if (!$order) {
            Log::warning("Order with Midtrans ID {$orderId} not found");
            return false;
        }

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                $this->orderService->updatePaymentStatus($order, 'challenge');
            } else if ($fraudStatus == 'accept') {
                $this->orderService->updatePaymentStatus($order, 'paid');
            }
        } else if ($transactionStatus == 'settlement') {
            $this->orderService->updatePaymentStatus($order, 'paid');
        } else if (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $this->orderService->updatePaymentStatus($order, 'failed');
        } else if ($transactionStatus == 'pending') {
            $this->orderService->updatePaymentStatus($order, 'pending');
        }

        return true;
    }

    /**
     * Verify Midtrans Signature.
     */
    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $serverKey, string $signatureKey): bool
    {
        $hash = hash("sha512", $orderId . $statusCode . $grossAmount . $serverKey);
        return $hash === $signatureKey;
    }
}
