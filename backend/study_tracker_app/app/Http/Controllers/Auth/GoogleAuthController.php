<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    use ApiResponse;

    /**
     * Handle Google OAuth authentication.
     */
    public function handle(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'name' => 'required|string',
            'email' => 'required|email',
            'google_id' => 'required|string',
        ]);

        // In a real implementation, you would verify the Google token here
        // For now, we'll use the provided data

        $user = User::where('google_id', $request->google_id)
            ->orWhere('email', $request->email)
            ->first();

        if (!$user) {
            // Create new user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'google_id' => $request->google_id,
                'password' => Hash::make(Str::random(32)), // Random password for Google users
                'email_verified_at' => now(),
            ]);

            // Create default preferences
            UserPreference::create([
                'user_id' => $user->id,
            ]);
        } else {
            // Update Google ID if not set
            if (!$user->google_id) {
                $user->update(['google_id' => $request->google_id]);
            }
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->successResponse([
            'user' => $user->makeHidden(['password', 'remember_token'])->load('preferences'),
            'token' => $token,
        ], 'Google authentication successful');
    }
}

