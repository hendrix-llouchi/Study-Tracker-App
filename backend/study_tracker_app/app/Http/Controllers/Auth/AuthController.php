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
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

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

