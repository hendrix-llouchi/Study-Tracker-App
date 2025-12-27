<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Use Laravel's built-in CORS middleware in global stack to handle OPTIONS preflight requests
        // This must run before routing to catch OPTIONS requests properly
        $middleware->prepend(\Illuminate\Http\Middleware\HandleCors::class);
        
        // Add Content Security Policy middleware for Google OAuth support
        $middleware->append(\App\Http\Middleware\ContentSecurityPolicy::class);
        
        // Enable stateful API for cookie-based authentication
        $middleware->statefulApi();

        $middleware->alias([
            'log.api' => \App\Http\Middleware\LogApiRequests::class,
        ]);

        // Rate limiting - temporarily disabled to fix connection issues
        // Can be re-enabled later with proper rate limiter configuration
        
        // CORS configuration - exempt API routes from CSRF
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Helper function to add CORS headers to response
        $addCorsHeaders = function ($response, $request) {
            $origin = $request->headers->get('Origin');
            $allowedPatterns = [
                'http://localhost:5173',
                'http://127.0.0.1:5173',
                'http://172.20.10.4:5173',
            ];
            
            $isAllowed = false;
            if ($origin) {
                if (preg_match('/^http:\/\/(localhost|127\.0\.0\.1|\d+\.\d+\.\d+\.\d+):5173$/', $origin)) {
                    $isAllowed = true;
                } elseif (in_array($origin, $allowedPatterns)) {
                    $isAllowed = true;
                }
            }
            
            $allowedOrigin = $isAllowed ? $origin : 'http://localhost:5173';
            
            $response->headers->set('Access-Control-Allow-Origin', $allowedOrigin, true);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS', true);
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-XSRF-TOKEN', true);
            $response->headers->set('Access-Control-Allow-Credentials', 'true', true);
            $response->headers->set('Access-Control-Max-Age', '86400', true);
            
            return $response;
        };
        
        // Helper function to add CSP headers
        $addCspHeaders = function ($response) {
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
            // Force override any existing header
            $response->headers->set('Content-Security-Policy', $cspHeader, true);
            return $response;
        };
        
        // Handle validation exceptions
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) use ($addCorsHeaders, $addCspHeaders) {
            if ($request->is('api/*')) {
                $response = response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                    'error_code' => 'VALIDATION_ERROR',
                    'timestamp' => now()->toIso8601String(),
                ], 422);
                $response = $addCorsHeaders($response, $request);
                return $addCspHeaders($response);
            }
        });
        
        // Handle other exceptions for API routes
        $exceptions->render(function (\Throwable $e, $request) use ($addCorsHeaders, $addCspHeaders) {
            if ($request->is('api/*')) {
                $response = response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'An error occurred',
                    'error_code' => 'SERVER_ERROR',
                    'timestamp' => now()->toIso8601String(),
                ], 500);
                $response = $addCorsHeaders($response, $request);
                return $addCspHeaders($response);
            }
        });
    })->create();
