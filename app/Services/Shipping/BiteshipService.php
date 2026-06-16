<?php

namespace App\Services\Shipping;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BiteshipService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.biteship.com';

    public function __construct()
    {
        $this->apiKey = config('services.biteship.api_key', '');
    }

    /**
     * Get shipping rates estimate from Biteship.
     */
    public function getRates(array $origin, array $destination, array $items): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/v1/rates/couriers", [
                'origin_latitude' => $origin['lat'],
                'origin_longitude' => $origin['lng'],
                'destination_latitude' => $destination['lat'],
                'destination_longitude' => $destination['lng'],
                'items' => $items, // items must have name, value, weight, quantity
            ]);

            if ($response->successful()) {
                return $response->json()['pricing'] ?? [];
            }

            Log::error('Biteship rates request failed: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Biteship error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Book shipping pickup and obtain tracking number & shipping label.
     */
    public function bookPickup(Order $order, array $pickupDetails, array $deliveryDetails): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/v1/orders", [
                'shipper_contact_name' => $pickupDetails['name'],
                'shipper_contact_phone' => $pickupDetails['phone'],
                'origin_address' => $pickupDetails['address'],
                'origin_coordinate' => [
                    'latitude' => $pickupDetails['lat'],
                    'longitude' => $pickupDetails['lng'],
                ],
                'recipient_contact_name' => $deliveryDetails['name'],
                'recipient_contact_phone' => $deliveryDetails['phone'],
                'destination_address' => $deliveryDetails['address'],
                'destination_coordinate' => [
                    'latitude' => $deliveryDetails['lat'],
                    'longitude' => $deliveryDetails['lng'],
                ],
                'courier_company' => $order->courier,
                'courier_type' => $order->shipping_service,
                'delivery_type' => 'now', // now, scheduled
                'items' => $order->items->map(fn($item) => [
                    'name' => $item->product_name_snapshot,
                    'qty' => $item->qty,
                    'value' => (int) $item->price_snapshot,
                    'weight' => (int) $item->product->weight, // weight in grams
                ])->toArray(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'biteship_order_id' => $data['id'],
                    'tracking_number' => $data['courier']['waybill_id'] ?? null,
                    'label_url' => $data['courier']['label_url'] ?? null,
                ];
            }

            Log::error('Biteship booking request failed: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Biteship error: ' . $e->getMessage());
        }

        return ['success' => false];
    }
}
