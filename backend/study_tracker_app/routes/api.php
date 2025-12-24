<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [App\Http\Controllers\Auth\AuthController::class, 'register']);
        Route::post('/login', [App\Http\Controllers\Auth\AuthController::class, 'login']);
        Route::post('/google', [App\Http\Controllers\Auth\GoogleAuthController::class, 'handle']);
        Route::post('/forgot-password', [App\Http\Controllers\Auth\PasswordResetController::class, 'sendResetLink']);
        Route::post('/reset-password', [App\Http\Controllers\Auth\PasswordResetController::class, 'reset']);
        
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [App\Http\Controllers\Auth\AuthController::class, 'logout']);
            Route::get('/me', [App\Http\Controllers\Auth\AuthController::class, 'me']);
        });
    });

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Dashboard routes
        Route::prefix('dashboard')->group(function () {
            Route::get('/stats', [App\Http\Controllers\DashboardController::class, 'stats']);
            Route::get('/upcoming-classes', [App\Http\Controllers\DashboardController::class, 'upcomingClasses']);
            Route::get('/study-streak', [App\Http\Controllers\DashboardController::class, 'studyStreak']);
            Route::get('/activities', [App\Http\Controllers\DashboardController::class, 'activities']);
        });

        // Course routes
        Route::apiResource('courses', App\Http\Controllers\CourseController::class);

        // Performance routes
        Route::prefix('performance')->group(function () {
            Route::get('/results', [App\Http\Controllers\PerformanceController::class, 'index']);
            Route::post('/results', [App\Http\Controllers\PerformanceController::class, 'store']);
            Route::post('/results/bulk', [App\Http\Controllers\PerformanceController::class, 'bulkUpload']);
            Route::get('/results/{id}', [App\Http\Controllers\PerformanceController::class, 'show']);
            Route::put('/results/{id}', [App\Http\Controllers\PerformanceController::class, 'update']);
            Route::delete('/results/{id}', [App\Http\Controllers\PerformanceController::class, 'destroy']);
            Route::get('/gpa-trend', [App\Http\Controllers\PerformanceController::class, 'gpaTrend']);
            Route::get('/subjects', [App\Http\Controllers\PerformanceController::class, 'subjects']);
        });

        // Study Planning routes
        Route::prefix('planning')->group(function () {
            Route::get('/plans', [App\Http\Controllers\StudyPlanController::class, 'index']);
            Route::post('/plans', [App\Http\Controllers\StudyPlanController::class, 'store']);
            Route::get('/plans/{id}', [App\Http\Controllers\StudyPlanController::class, 'show']);
            Route::put('/plans/{id}', [App\Http\Controllers\StudyPlanController::class, 'update']);
            Route::delete('/plans/{id}', [App\Http\Controllers\StudyPlanController::class, 'destroy']);
            Route::patch('/plans/{id}/complete', [App\Http\Controllers\StudyPlanController::class, 'complete']);
            Route::post('/plans/check-conflicts', [App\Http\Controllers\StudyPlanController::class, 'checkConflicts']);
        });

        // Timetable routes
        Route::prefix('timetable')->group(function () {
            Route::get('/', [App\Http\Controllers\TimetableController::class, 'index']);
            Route::post('/', [App\Http\Controllers\TimetableController::class, 'store']);
            Route::post('/upload', [App\Http\Controllers\TimetableController::class, 'upload']);
            Route::put('/classes/{id}', [App\Http\Controllers\TimetableController::class, 'updateClass']);
            Route::delete('/classes/{id}', [App\Http\Controllers\TimetableController::class, 'deleteClass']);
        });

        // Assignment routes
        Route::apiResource('assignments', App\Http\Controllers\AssignmentController::class);
        Route::patch('/assignments/{id}/complete', [App\Http\Controllers\AssignmentController::class, 'complete']);

        // Progress & Reports routes
        Route::prefix('progress')->group(function () {
            Route::get('/weekly', [App\Http\Controllers\ProgressController::class, 'weekly']);
            Route::post('/weekly/generate', [App\Http\Controllers\ProgressController::class, 'generateWeekly']);
            Route::get('/analytics', [App\Http\Controllers\ProgressController::class, 'analytics']);
        });

        // Notification routes
        Route::prefix('notifications')->group(function () {
            Route::get('/', [App\Http\Controllers\NotificationController::class, 'index']);
            Route::patch('/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead']);
            Route::post('/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
        });

        // Settings routes
        Route::prefix('settings')->group(function () {
            Route::get('/profile', [App\Http\Controllers\SettingsController::class, 'profile']);
            Route::put('/profile', [App\Http\Controllers\SettingsController::class, 'updateProfile']);
            Route::post('/avatar', [App\Http\Controllers\SettingsController::class, 'uploadAvatar']);
            Route::get('/preferences', [App\Http\Controllers\SettingsController::class, 'preferences']);
            Route::put('/preferences', [App\Http\Controllers\SettingsController::class, 'updatePreferences']);
            Route::post('/change-password', [App\Http\Controllers\SettingsController::class, 'changePassword']);
            Route::post('/export-data', [App\Http\Controllers\SettingsController::class, 'exportData']);
            Route::delete('/account', [App\Http\Controllers\SettingsController::class, 'deleteAccount']);
        });

        // AI Coach routes (Phase 2)
        Route::prefix('ai-coach')->group(function () {
            Route::post('/chat', [App\Http\Controllers\AiCoachController::class, 'chat']);
            Route::get('/history', [App\Http\Controllers\AiCoachController::class, 'history']);
        });

        // File upload routes
        Route::prefix('files')->group(function () {
            Route::post('/upload', [App\Http\Controllers\FileUploadController::class, 'upload']);
        });
    });
});

