<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class RequestLogMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);
        $response = $next($request);
        $durationMs = (int) round((microtime(true) - $start) * 1000);

        Log::channel('request')->info('API request', [
            'method' => $request->method(),
            'uri' => $request->getRequestUri(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'status' => $response->getStatusCode(),
            'duration_ms' => $durationMs,
        ]);

        return $response;
    }
}
