<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class VerifyBiteshipSignature
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('X-Biteship-Signature');
        $expectedSignature = config('services.biteship.webhook_signature');

        if ($expectedSignature && $signature !== $expectedSignature) {
            Log::warning('Biteship webhook signature verification failed');
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 403);
        }

        return $next($request);
    }
}
