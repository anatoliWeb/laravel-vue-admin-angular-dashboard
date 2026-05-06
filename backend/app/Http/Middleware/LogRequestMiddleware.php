<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogRequestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * WHY:
     * Provides centralized logging for all API requests
     * to improve debugging, monitoring and performance tracking.
     */
    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);

        $response = $next($request);

        $duration = round((microtime(true) - $start) * 1000, 2);

        Log::info('API Request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),

            // WHY:
            // Track authenticated user if available
            'user_id' => optional($request->user())->id,

            'status' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'ip' => $request->ip(),
        ]);

        return $response;
    }
}
