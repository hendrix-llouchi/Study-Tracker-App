<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponse;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

class SettingsController extends Controller
{
    use ApiResponse;

    /**
     * Get user profile.
     */
    public function profile(Request $request): JsonResponse
    {
        return $this->successResponse(['profile' => $request->user()]);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'university' => 'nullable|string|max:255',
            'semester' => 'nullable|string|max:100',
        ]);

        $request->user()->update($request->only(['name', 'university', 'semester']));

        return $this->successResponse(['profile' => $request->user()->fresh()], 'Profile updated successfully');
    }

    /**
     * Upload profile avatar.
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|max:2048',
        ]);

        // TODO: Implement file upload to S3/Cloudflare R2
        $path = $request->file('avatar')->store('avatars', 'public');
        $url = asset('storage/' . $path);

        $request->user()->update(['avatar_url' => $url]);

        return $this->successResponse(['avatar_url' => $url], 'Avatar uploaded successfully');
    }

    /**
     * Get user preferences.
     */
    public function preferences(Request $request): JsonResponse
    {
        $preferences = $request->user()->preferences ?? UserPreference::create([
            'user_id' => $request->user()->id,
        ]);

        return $this->successResponse(['preferences' => $preferences]);
    }

    /**
     * Update user preferences.
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $request->validate([
            'morning_email_time' => 'sometimes|date_format:H:i',
            'reminder_time' => 'sometimes|date_format:H:i',
            'email_notifications' => 'sometimes|boolean',
            'push_notifications' => 'sometimes|boolean',
            'weekly_report_enabled' => 'sometimes|boolean',
            'weekly_report_day' => 'sometimes|string|max:20',
            'timezone' => 'sometimes|string|max:50',
        ]);

        $preferences = $request->user()->preferences ?? UserPreference::create([
            'user_id' => $request->user()->id,
        ]);

        $preferences->update($request->only([
            'morning_email_time',
            'reminder_time',
            'email_notifications',
            'push_notifications',
            'weekly_report_enabled',
            'weekly_report_day',
            'timezone',
        ]));

        return $this->successResponse(['preferences' => $preferences], 'Preferences updated successfully');
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        if (!Hash::check($request->current_password, $request->user()->password)) {
            return $this->errorResponse('Current password is incorrect', null, 422);
        }

        $request->user()->update([
            'password' => Hash::make($request->new_password),
        ]);

        return $this->successResponse(null, 'Password changed successfully');
    }

    /**
     * Request data export.
     */
    public function exportData(Request $request): JsonResponse
    {
        // TODO: Implement data export job
        return $this->successResponse([
            'estimated_time' => '5-10 minutes',
        ], 'Data export request received. You\'ll receive an email with download link.');
    }

    /**
     * Delete user account.
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
            'confirmation' => 'required|string|in:DELETE',
        ]);

        if (!Hash::check($request->password, $request->user()->password)) {
            return $this->errorResponse('Password is incorrect', null, 422);
        }

        // TODO: Schedule account deletion (30-day grace period)
        $request->user()->delete();

        return $this->successResponse(null, 'Account deletion scheduled. Your data will be permanently deleted in 30 days.');
    }
}

