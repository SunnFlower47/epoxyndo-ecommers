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
        $this->biteshipWebhookService->handleNotification($payload);

        // Biteship requires a 200 OK response upon installation (ping)
        // even if the payload is empty or invalid.
        return response()->json(['status' => 'success', 'message' => 'Notification received']);
    }
}
