<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Use Laravel's built-in CORS middleware (reads from config/cors.php)
        // This handles CORS for all API routes and Sanctum CSRF cookie route
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

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
        
        // Handle validation exceptions
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) use ($addCorsHeaders) {
            if ($request->is('api/*')) {
                $response = response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                    'error_code' => 'VALIDATION_ERROR',
                    'timestamp' => now()->toIso8601String(),
                ], 422);
                return $addCorsHeaders($response, $request);
            }
        });
        
        // Handle other exceptions for API routes
        $exceptions->render(function (\Throwable $e, $request) use ($addCorsHeaders) {
            if ($request->is('api/*')) {
                $response = response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'An error occurred',
                    'error_code' => 'SERVER_ERROR',
                    'timestamp' => now()->toIso8601String(),
                ], 500);
                return $addCorsHeaders($response, $request);
            }
        });
    })->create();
