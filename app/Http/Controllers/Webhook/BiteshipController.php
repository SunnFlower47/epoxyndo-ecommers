<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\Webhook\BiteshipWebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BiteshipController extends Controller
{
    public function __construct(protected BiteshipWebhookService $biteshipWebhookService)
    {
    }

    /**
     * Handle incoming shipment tracking webhook from Biteship.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        // Handle signature / token verification if configured
        $success = $this->biteshipWebhookService->handleNotification($payload);

        if ($success) {
            return response()->json(['status' => 'success', 'message' => 'Notification processed successfully']);
        }

        return response()->json(['status' => 'error', 'message' => 'Failed to process notification'], 400);
    }
}
