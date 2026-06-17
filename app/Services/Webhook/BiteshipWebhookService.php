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

        $biteshipOrderId = $payload['order_id'] ?? $payload['id'] ?? null;
        $status = $payload['status'] ?? null; 
        
        // Biteship webhook usually sends waybill_id inside courier object
        $waybillNumber = $payload['courier_tracking_id'] ?? $payload['courier']['waybill_id'] ?? $payload['courier']['tracking_id'] ?? null;

        if (!$biteshipOrderId) {
            Log::warning("Biteship webhook missing order_id");
            return false;
        }

        $shipment = Shipment::where('biteship_order_id', $biteshipOrderId)->first();

        if (!$shipment) {
            Log::warning("Shipment with biteship_order_id {$biteshipOrderId} not found");
            return false;
        }

        $updateData = ['status' => $this->mapStatus($status)];
        
        // Update tracking number if we receive it from webhook
        if ($waybillNumber && !$shipment->tracking_number) {
            $updateData['tracking_number'] = $waybillNumber;
            $updateData['biteship_tracking_id'] = $waybillNumber;
        }

        $shipment->update($updateData);

        // If delivered, update order status as well
        if ($updateData['status'] === 'delivered') {
            $shipment->order->update(['status' => 'completed']);
        } else if ($updateData['status'] === 'shipping') {
            $shipment->order->update(['status' => 'shipped']);
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
