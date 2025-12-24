<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log activity for authenticated users
        if ($request->user()) {
            ActivityLog::create([
                'user_id' => $request->user()->id,
                'log_name' => 'api_request',
                'description' => $request->method() . ' ' . $request->path(),
                'event' => $request->method(),
                'properties' => [
                    'path' => $request->path(),
                    'method' => $request->method(),
                    'status' => $response->getStatusCode(),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $response;
    }
}

