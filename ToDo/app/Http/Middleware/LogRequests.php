<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LogRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = (string) Str::uuid();
        $startTime = microtime(true);

        // Share context across all log channels for this request
        Log::shareContext([
            'request_id' => $requestId,
            'user_id' => $request->user()?->id,
            'user_email' => $request->user()?->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => $request->session()->getId(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'route' => $request->route()?->getName(),
        ]);

        // Log incoming request
        Log::info('Incoming request', [
            'path' => $request->path(),
            'query_params' => $request->query(),
            'has_file' => $request->hasFile('*'),
        ]);

        $response = $next($request);

        // Calculate response time
        $responseTime = round((microtime(true) - $startTime) * 1000, 2); // in milliseconds

        // Log response
        Log::info('Request completed', [
            'status_code' => $response->getStatusCode(),
            'response_time_ms' => $responseTime,
        ]);

        // Add request ID to response headers
        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }
}
