<?php

namespace App\Services;

use App\Models\Order;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Http;
use Exception;

class BiteshipService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.biteship.api_key');
        // Gunakan baseUrl v1
        $this->baseUrl = 'https://api.biteship.com/v1';
    }

    /**
     * Membuat pesanan pengiriman di Biteship (Request Pickup)
     */
    public function createOrder(Order $order)
    {
        $settings = app(GeneralSettings::class);

        // Jika alamat tidak lengkap, lemparkan error
        if (!$order->shipping_address || !isset($order->shipping_address['full_address'])) {
            throw new Exception("Alamat pengiriman belum diisi secara lengkap pada pesanan ini.");
        }

        if (!$order->courier || !$order->courier_service) {
            throw new Exception("Kurir atau layanan kurir belum dipilih.");
        }

        // Siapkan Items
        $items = $order->items->map(function ($item) {
            return [
                'name' => $item->product_name ?? 'Produk Epoxyndo',
                'description' => '',
                'value' => (int) $item->unit_price,
                'quantity' => (int) $item->quantity,
                'weight' => (int) ($item->product->weight ?? 1000), // Default 1kg jika kosong
            ];
        })->toArray();

        // Hitung total berat
        $totalWeight = array_sum(array_column($items, 'weight'));

        $payload = [
            // Data Pengirim (Origin)
            'shipper_contact_name' => $settings->company_name ?? 'Admin',
            'shipper_contact_phone' => $settings->support_phone ?? '081234567890',
            'shipper_contact_email' => $settings->support_email ?? 'admin@epoxyndo.com',
            'shipper_organization' => $settings->company_name ?? 'Epoxyndo',
            'origin_contact_name' => $settings->company_name ?? 'Admin',
            'origin_contact_phone' => $settings->support_phone ?? '081234567890',
            'origin_address' => $settings->company_address ?? 'Jl. Default Address',

            // Data Penerima (Destination)
            'destination_contact_name' => $order->shipping_address['name'] ?? $order->user->name,
            'destination_contact_phone' => $order->shipping_address['phone'] ?? '-',
            'destination_contact_email' => $order->user->email,
            'destination_address' => $order->shipping_address['full_address'],

            // Info Pengiriman
            'courier_company' => strtolower($order->courier),
            'courier_type' => strtolower($order->courier_service),
            'delivery_type' => 'now', // Tipe pickup 'now' atau 'later'

            // Barang
            'items' => $items,
        ];

        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/orders", $payload);

        if ($response->failed()) {
            throw new Exception('Biteship Error: ' . $response->body());
        }

        $data = $response->json();

        // Simpan ke tabel shipments
        $shipment = $order->shipment()->create([
            'courier_name' => $order->courier,
            'courier_service' => $order->courier_service,
            'tracking_number' => $data['courier']['tracking_id'] ?? null,
            'biteship_order_id' => $data['id'] ?? null,
            'biteship_tracking_id' => $data['courier']['tracking_id'] ?? null,
            'status' => $data['status'] ?? 'placed',
            'shipping_address' => $order->shipping_address,
        ]);

        // Kirim email notifikasi bahwa barang sedang dikirim (termasuk resi)
        try {
            \Illuminate\Support\Facades\Mail::to($order->customer_email ?? $order->user?->email)
                ->queue(new \App\Mail\OrderShippedMail($order, $shipment));
        } catch (\Exception $e) {
            // Log error email but don't stop the pickup process
            \Illuminate\Support\Facades\Log::error('Failed to send OrderShippedMail: ' . $e->getMessage());
        }

        return $shipment;
    }
}
