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
        // #region agent log
        $logData = ['origin' => $request->headers->get('Origin'), 'method' => $request->getMethod(), 'path' => $request->path(), 'sanctum_stateful' => config('sanctum.stateful', []), 'session_domain' => config('session.domain'), 'timestamp' => time()];
        $logPath = 'c:\xampp\htdocs\Study-Tracker-App\.cursor\debug.log';
        $logEntry = json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A,B', 'location' => 'HandleCors.php:16', 'message' => 'CORS middleware entry', 'data' => $logData, 'timestamp' => time() * 1000]) . "\n";
        @file_put_contents($logPath, $logEntry, FILE_APPEND | LOCK_EX);
        \Log::info('CORS Debug', ['data' => $logData]);
        // #endregion
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
        
        // #region agent log
        $logPath = 'c:\xampp\htdocs\Study-Tracker-App\.cursor\debug.log';
        $logEntry = json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A,D,E', 'location' => 'HandleCors.php:45', 'message' => 'Origin validation result', 'data' => ['origin' => $origin, 'isAllowed' => $isAllowed, 'allowedOrigin' => $allowedOrigin, 'timestamp' => time() * 1000], 'timestamp' => time() * 1000]) . "\n";
        @file_put_contents($logPath, $logEntry, FILE_APPEND | LOCK_EX);
        \Log::info('CORS Origin Validation', ['origin' => $origin, 'isAllowed' => $isAllowed, 'allowedOrigin' => $allowedOrigin]);
        // #endregion
        
        // Handle preflight requests - must return response with headers immediately
        if ($request->getMethod() === 'OPTIONS') {
            // #region agent log
            $logPath = 'c:\xampp\htdocs\Study-Tracker-App\.cursor\debug.log';
            $logEntry = json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'E', 'location' => 'HandleCors.php:52', 'message' => 'OPTIONS preflight response', 'data' => ['allowedOrigin' => $allowedOrigin, 'credentials' => 'true', 'timestamp' => time() * 1000], 'timestamp' => time() * 1000]) . "\n";
            @file_put_contents($logPath, $logEntry, FILE_APPEND | LOCK_EX);
            \Log::info('CORS Preflight', ['allowedOrigin' => $allowedOrigin]);
            // #endregion
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

        // #region agent log
        $finalHeaders = ['Access-Control-Allow-Origin' => $response->headers->get('Access-Control-Allow-Origin'), 'Access-Control-Allow-Credentials' => $response->headers->get('Access-Control-Allow-Credentials')];
        $logPath = 'c:\xampp\htdocs\Study-Tracker-App\.cursor\debug.log';
        $logEntry = json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D,E', 'location' => 'HandleCors.php:80', 'message' => 'CORS headers set on response', 'data' => array_merge($finalHeaders, ['status' => $response->getStatusCode(), 'timestamp' => time() * 1000]), 'timestamp' => time() * 1000]) . "\n";
        @file_put_contents($logPath, $logEntry, FILE_APPEND | LOCK_EX);
        \Log::info('CORS Response Headers', array_merge($finalHeaders, ['status' => $response->getStatusCode()]));
        // #endregion

        return $response;
    }
}
