<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Traits\ApiResponse;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'university' => $request->university,
            ]);

            // Create default preferences
            UserPreference::create([
                'user_id' => $user->id,
                'timezone' => $request->timezone ?? 'UTC',
            ]);

            $token = $user->createToken('auth-token')->plainTextToken;

            return $this->successResponse([
                'user' => $user->makeHidden(['password', 'remember_token'])->load('preferences'),
                'token' => $token,
            ], 'Registration successful', 201);
        } catch (\Exception $e) {
            \Log::error('Registration error: ' . $e->getMessage());
            return $this->errorResponse('Registration failed: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Login user.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // #region agent log
        $logPath = 'c:\xampp\htdocs\Study-Tracker-App\.cursor\debug.log';
        $logEntry = json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A,B,C', 'location' => 'AuthController.php:56', 'message' => 'Login method called', 'data' => ['email' => $request->email, 'origin' => $request->headers->get('Origin'), 'timestamp' => time() * 1000], 'timestamp' => time() * 1000]) . "\n";
        @file_put_contents($logPath, $logEntry, FILE_APPEND | LOCK_EX);
        \Log::info('Login Attempt', ['email' => $request->email, 'origin' => $request->headers->get('Origin')]);
        // #endregion
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // #region agent log
            $logPath = 'c:\xampp\htdocs\Study-Tracker-App\.cursor\debug.log';
            $logEntry = json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A', 'location' => 'AuthController.php:61', 'message' => 'User not found', 'data' => ['email' => $request->email, 'timestamp' => time() * 1000], 'timestamp' => time() * 1000]) . "\n";
            @file_put_contents($logPath, $logEntry, FILE_APPEND | LOCK_EX);
            // #endregion
            throw ValidationException::withMessages([
                'email' => ['The provided email address is not registered.'],
            ]);
        }

        if (!Hash::check($request->password, $user->password)) {
            // #region agent log
            $logPath = 'c:\xampp\htdocs\Study-Tracker-App\.cursor\debug.log';
            $logEntry = json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A', 'location' => 'AuthController.php:67', 'message' => 'Invalid password', 'data' => ['email' => $request->email, 'timestamp' => time() * 1000], 'timestamp' => time() * 1000]) . "\n";
            @file_put_contents($logPath, $logEntry, FILE_APPEND | LOCK_EX);
            // #endregion
            throw ValidationException::withMessages([
                'password' => ['The password you entered is incorrect.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        // #region agent log
        $logPath = 'c:\xampp\htdocs\Study-Tracker-App\.cursor\debug.log';
        $logEntry = json_encode(['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A', 'location' => 'AuthController.php:73', 'message' => 'Login successful', 'data' => ['userId' => $user->id, 'tokenCreated' => true, 'timestamp' => time() * 1000], 'timestamp' => time() * 1000]) . "\n";
        @file_put_contents($logPath, $logEntry, FILE_APPEND | LOCK_EX);
        \Log::info('Login Success', ['userId' => $user->id]);
        // #endregion

        return $this->successResponse([
            'user' => $user->makeHidden(['password', 'remember_token'])->load('preferences'),
            'token' => $token,
        ], 'Login successful');
    }

    /**
     * Logout user.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully');
    }

    /**
     * Get authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('preferences');

        return $this->successResponse([
            'user' => $user->makeHidden(['password', 'remember_token']),
        ]);
    }
}

