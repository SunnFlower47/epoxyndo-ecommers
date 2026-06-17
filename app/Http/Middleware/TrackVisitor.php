<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\VisitorLog;
use Illuminate\Support\Facades\Cache;

class TrackVisitor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        
        // Cache key based on IP and User Agent to prevent logging every single click
        // Only log once per 24 hours per unique device/IP
        $cacheKey = 'visitor_' . md5($ip . $userAgent);
        
        if (!Cache::has($cacheKey)) {
            // Ignore common bots if possible (basic check)
            if (!preg_match('/bot|crawl|slurp|spider|mediapartners/i', $userAgent)) {
                VisitorLog::create([
                    'ip_address' => $ip,
                    'user_agent' => substr($userAgent, 0, 255),
                    'visited_url' => substr($request->fullUrl(), 0, 255),
                    'user_id' => auth()->id(),
                ]);
            }
            // Set cache for 24 hours
            Cache::put($cacheKey, true, now()->addHours(24));
        }

        return $next($request);
    }
}
