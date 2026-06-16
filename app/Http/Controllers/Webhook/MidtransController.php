<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\Webhook\MidtransWebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MidtransController extends Controller
{
    public function __construct(protected MidtransWebhookService $midtransWebhookService)
    {
    }

    /**
     * Handle incoming payment notification webhook from Midtrans.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        // Custom validation or signature verification can also be run via Middleware
        $success = $this->midtransWebhookService->handleNotification($payload);

        if ($success) {
            return response()->json(['status' => 'success', 'message' => 'Notification processed successfully']);
        }

        return response()->json(['status' => 'error', 'message' => 'Failed to process notification'], 400);
    }
}
