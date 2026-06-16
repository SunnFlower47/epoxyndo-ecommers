<?php

namespace App\Services\Webhook;

use App\Models\Shipment;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class BiteshipWebhookService
{
    /**
     * Handle shipping tracking notification from Biteship.
     */
    public function handleNotification(array $payload): bool
    {
        Log::info('Biteship Webhook Payload:', $payload);

        $biteshipOrderId = $payload['order_id'] ?? null;
        $waybillNumber = $payload['waybill_number'] ?? null;
        $status = $payload['status'] ?? null; // e.g. picking_up, picked_up, delivering, delivered

        if (!$waybillNumber) {
            return false;
        }

        $shipment = Shipment::where('tracking_number', $waybillNumber)->first();

        if (!$shipment) {
            Log::warning("Shipment with tracking number {$waybillNumber} not found");
            return false;
        }

        // Map Biteship status to internal shipment status
        $mappedStatus = $this->mapStatus($status);
        $shipment->update(['status' => $mappedStatus]);

        // If delivered, update order status as well
        if ($mappedStatus === 'delivered') {
            $shipment->order->update(['status' => 'completed']);
        }

        return true;
    }

    /**
     * Map Biteship status to our internal shipment status.
     */
    protected function mapStatus(?string $status): string
    {
        switch ($status) {
            case 'allocated':
            case 'picking_up':
                return 'pending';
            case 'picked_up':
                return 'picked_up';
            case 'delivering':
                return 'shipping';
            case 'delivered':
                return 'delivered';
            case 'cancelled':
            case 'rejected':
                return 'cancelled';
            default:
                return 'unknown';
        }
    }
}
