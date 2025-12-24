<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleCors
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->headers->get('Origin');
        
        // Allow any origin on port 5173 (for development)
        // In production, you should restrict this to specific domains
        $allowedPatterns = [
            'http://localhost:5173',
            'http://127.0.0.1:5173',
            'http://172.20.10.4:5173', // Common local network IP
        ];
        
        // Check if origin matches allowed patterns or is localhost/127.0.0.1 on port 5173
        $isAllowed = false;
        if ($origin) {
            // Allow any IP address on port 5173 for development
            if (preg_match('/^http:\/\/(localhost|127\.0\.0\.1|\d+\.\d+\.\d+\.\d+):5173$/', $origin)) {
                $isAllowed = true;
            } elseif (in_array($origin, $allowedPatterns)) {
                $isAllowed = true;
            }
        }
        
        // If no origin or not allowed, use default
        $allowedOrigin = $isAllowed ? $origin : 'http://localhost:5173';
        
        // Handle preflight requests - must return response with headers immediately
        if ($request->getMethod() === 'OPTIONS') {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', $allowedOrigin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-XSRF-TOKEN')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400');
        }
        
        // Process the actual request
        $response = $next($request);

        // Set CORS headers on response (must be exact origin, not wildcard, when credentials are used)
        $response->headers->set('Access-Control-Allow-Origin', $allowedOrigin, true);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS', true);
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-XSRF-TOKEN', true);
        $response->headers->set('Access-Control-Allow-Credentials', 'true', true);
        $response->headers->set('Access-Control-Max-Age', '86400', true);
        
        // Remove any wildcard CORS headers that might have been set by other middleware
        if ($response->headers->get('Access-Control-Allow-Origin') === '*') {
            $response->headers->remove('Access-Control-Allow-Origin');
            $response->headers->set('Access-Control-Allow-Origin', $allowedOrigin, true);
        }

        return $response;
    }
}
