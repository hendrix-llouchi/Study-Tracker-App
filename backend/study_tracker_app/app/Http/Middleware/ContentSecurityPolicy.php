<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Remove any existing CSP headers to avoid conflicts
        $response->headers->remove('Content-Security-Policy');
        $response->headers->remove('X-Content-Security-Policy');
        $response->headers->remove('Content-Security-Policy-Report-Only');

        // Build CSP policy with all required directives for Google OAuth
        $csp = [
            "default-src 'self'",
            // Ensure 'unsafe-eval' is added if Google Identity needs it (common for some loaders)
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' 'report-sample' blob: https://accounts.google.com https://apis.google.com",
            "script-src-elem 'self' 'unsafe-inline' 'report-sample' blob: https://accounts.google.com https://apis.google.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data: blob: https:",
            // Added googleapis.com to connect-src
            "connect-src 'self' https://accounts.google.com https://*.googleapis.com http://localhost:8000 http://127.0.0.1:8000 http://localhost:5173 http://127.0.0.1:5173",
            "frame-src 'self' https://accounts.google.com",
            "frame-ancestors 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "upgrade-insecure-requests",
        ];

        $cspHeader = implode('; ', $csp);

        // Set CSP header (this will override any previous CSP)
        $response->headers->set('Content-Security-Policy', $cspHeader, true);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        return $response;
    }
}

