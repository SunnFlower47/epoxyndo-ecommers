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

        // Biteship webhook can sometimes wrap payload in 'data' object
        $data = $payload['data'] ?? $payload;

        $biteshipOrderId = $data['order_id'] ?? $data['id'] ?? null;
        $status = $data['status'] ?? null; 
        
        // waybill_id adalah Resi Asli (contoh: JNE12345)
        $waybillNumber = $data['waybill_id'] ?? ($data['courier']['waybill_id'] ?? null);
        
        // tracking_id adalah ID internal Biteship (contoh: FOSix...)
        $biteshipTrackingId = $data['courier_tracking_id'] ?? ($data['courier']['tracking_id'] ?? null);

        // Sometimes ping sends empty order_id
        if (!$biteshipOrderId) {
            return false;
        }

        $shipment = Shipment::where('biteship_order_id', $biteshipOrderId)->first();

        if (!$shipment) {
            Log::warning("Shipment with biteship_order_id {$biteshipOrderId} not found");
            return false;
        }

        $updateData = [];
        
        // Hanya update status jika status dari webhook tidak kosong
        if ($status) {
            $updateData['status'] = $this->mapStatus($status);
        }
        
        // Update tracking number if we receive the ACTUAL waybill
        if ($waybillNumber && !$shipment->tracking_number) {
            $updateData['tracking_number'] = $waybillNumber;
        }

        if ($biteshipTrackingId && !$shipment->biteship_tracking_id) {
            $updateData['biteship_tracking_id'] = $biteshipTrackingId;
        }

        if (!empty($updateData)) {
            $shipment->update($updateData);
        }

        // If delivered, update order status as well
        if (isset($updateData['status'])) {
            if ($updateData['status'] === 'delivered') {
                $shipment->order->update(['status' => 'completed']);
            } else if ($updateData['status'] === 'shipping') {
                $shipment->order->update(['status' => 'shipped']);
            }
        }

        return true;
    }

    /**
     * Map Biteship status to our internal shipment status.
     */
    protected function mapStatus(?string $status): string
    {
        switch ($status) {
            case 'placed':
            case 'scheduled':
            case 'confirmed':
            case 'allocated':
            case 'picking_up':
            case 'pickingUp':
                return 'pending';
            case 'picked_up':
            case 'picked':
                return 'picked_up';
            case 'dropping_off':
            case 'delivering':
            case 'inTransit':
                return 'shipping';
            case 'delivered':
                return 'delivered';
            case 'cancelled':
            case 'rejected':
            case 'disposed':
            case 'returned':
                return 'cancelled';
            default:
                // Biarkan status yang tidak dikenal tetap masuk log tapi fallback ke pending atau unknown
                Log::warning("Unknown Biteship status received: " . $status);
                return 'unknown';
        }
    }
}
