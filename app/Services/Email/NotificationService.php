<?php

namespace App\Services\Email;

use App\Models\Order;
use Resend\Laravel\Facades\Resend;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send order placement confirmation email.
     */
    public function sendOrderPlacedEmail(Order $order): void
    {
        try {
            Resend::emails()->send([
                'from' => config('mail.from.address', 'epoxyndo@resend.dev'),
                'to' => $order->user->email,
                'subject' => 'Order Terbuat - PT Epoxyndo Art Lestari #' . $order->id,
                'html' => '<p>Halo ' . $order->user->name . ', pesanan Anda #' . $order->id . ' telah dibuat. Silakan selesaikan pembayaran.</p>',
            ]);
        } catch (\Exception $e) {
            Log::error('Resend Error (Order Placed): ' . $e->getMessage());
        }
    }

    /**
     * Send payment success email.
     */
    public function sendPaymentSuccessEmail(Order $order): void
    {
        try {
            Resend::emails()->send([
                'from' => config('mail.from.address', 'epoxyndo@resend.dev'),
                'to' => $order->user->email,
                'subject' => 'Pembayaran Berhasil - PT Epoxyndo Art Lestari #' . $order->id,
                'html' => '<p>Halo ' . $order->user->name . ', pembayaran untuk pesanan Anda #' . $order->id . ' telah diterima.</p>',
            ]);
        } catch (\Exception $e) {
            Log::error('Resend Error (Payment Success): ' . $e->getMessage());
        }
    }

    /**
     * Send shipment confirmation email with tracking number.
     */
    public function sendOrderShippedEmail(Order $order, string $trackingNumber): void
    {
        try {
            Resend::emails()->send([
                'from' => config('mail.from.address', 'epoxyndo@resend.dev'),
                'to' => $order->user->email,
                'subject' => 'Pesanan Dikirim - PT Epoxyndo Art Lestari #' . $order->id,
                'html' => '<p>Halo ' . $order->user->name . ', pesanan Anda #' . $order->id . ' telah dikirim dengan nomor resi: <strong>' . $trackingNumber . '</strong>.</p>',
            ]);
        } catch (\Exception $e) {
            Log::error('Resend Error (Order Shipped): ' . $e->getMessage());
        }
    }
}
