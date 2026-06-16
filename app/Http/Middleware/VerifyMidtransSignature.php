<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Webhook\MidtransWebhookService;
use Illuminate\Support\Facades\Log;

class VerifyMidtransSignature
{
    public function __construct(protected MidtransWebhookService $midtransWebhookService)
    {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $orderId = $request->input('order_id');
        $statusCode = $request->input('status_code');
        $grossAmount = $request->input('gross_amount');
        $signatureKey = $request->input('signature_key');

        $serverKey = config('services.midtrans.server_key');

        if (!$orderId || !$statusCode || !$grossAmount || !$signatureKey) {
            Log::warning('Midtrans signature verification failed: Missing required fields');
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $isValid = $this->midtransWebhookService->verifySignature(
            $orderId,
            $statusCode,
            $grossAmount,
            $serverKey,
            $signatureKey
        );

        if (!$isValid) {
            Log::warning("Midtrans signature key verification failed for order: {$orderId}");
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 403);
        }

        return $next($request);
    }
}
