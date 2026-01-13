<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\StudyPlanController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AiCoachController;
use App\Http\Controllers\FileUploadController;

/*
|--------------------------------------------------------------------------
| API Routes - v1
|--------------------------------------------------------------------------
|
| All routes are automatically prefixed with 'api/v1' via bootstrap/app.php
|
*/

// Health check endpoint (no auth required)
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is running',
        'timestamp' => now()->toISOString()
    ]);
});

// ============================================
// PUBLIC ROUTES (No Authentication Required)
// ============================================
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
    Route::post('/reset-password', [PasswordResetController::class, 'reset']);
    Route::post('/google', [AuthController::class, 'googleAuth']);
});

// ============================================
// PROTECTED ROUTES (Require Authentication)
// ============================================
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
    // Dashboard routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'stats']);
        Route::get('/upcoming-classes', [DashboardController::class, 'upcomingClasses']);
        Route::get('/study-streak', [DashboardController::class, 'studyStreak']);
        Route::get('/activities', [DashboardController::class, 'activities']);
    });
    
    // Course routes
    Route::apiResource('courses', CourseController::class);
    
    // Performance routes
    Route::prefix('performance')->group(function () {
        Route::get('/results', [PerformanceController::class, 'index']);
        Route::post('/results', [PerformanceController::class, 'store']);
        Route::get('/results/{id}', [PerformanceController::class, 'show']);
        Route::put('/results/{id}', [PerformanceController::class, 'update']);
        Route::delete('/results/{id}', [PerformanceController::class, 'destroy']);
        Route::post('/results/bulk', [PerformanceController::class, 'bulkUpload']);
        Route::post('/results/bulk-pdfs', [PerformanceController::class, 'bulkUploadPdfs']);
        Route::get('/gpa-trend', [PerformanceController::class, 'gpaTrend']);
        Route::get('/subjects', [PerformanceController::class, 'subjects']);
    });
    
    // Academic results routes
    Route::prefix('academic-results')->group(function () {
        Route::post('/bulk', [PerformanceController::class, 'bulkStore']);
    });
    
    // Study planning routes
    Route::prefix('planning')->group(function () {
        Route::get('/plans', [StudyPlanController::class, 'index']);
        Route::post('/plans', [StudyPlanController::class, 'store']);
        Route::get('/plans/{id}', [StudyPlanController::class, 'show']);
        Route::put('/plans/{id}', [StudyPlanController::class, 'update']);
        Route::delete('/plans/{id}', [StudyPlanController::class, 'destroy']);
        Route::patch('/plans/{id}/complete', [StudyPlanController::class, 'complete']);
        Route::post('/plans/check-conflicts', [StudyPlanController::class, 'checkConflicts']);
    });
    
    // Timetable routes
    Route::prefix('timetable')->group(function () {
        Route::get('/', [TimetableController::class, 'index']);
        Route::post('/', [TimetableController::class, 'store']);
        Route::post('/upload', [TimetableController::class, 'upload']);
        Route::put('/classes/{id}', [TimetableController::class, 'updateClass']);
        Route::delete('/classes/{id}', [TimetableController::class, 'deleteClass']);
    });
    
    // Assignment routes
    Route::apiResource('assignments', AssignmentController::class);
    Route::patch('/assignments/{id}/complete', [AssignmentController::class, 'complete']);
    
    // Progress routes
    Route::prefix('progress')->group(function () {
        Route::get('/weekly', [ProgressController::class, 'weekly']);
        Route::post('/weekly/generate', [ProgressController::class, 'generateWeekly']);
        Route::get('/analytics', [ProgressController::class, 'analytics']);
    });
    
    // Notification routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::patch('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    });
    
    // Settings routes
    Route::prefix('settings')->group(function () {
        Route::get('/profile', [SettingsController::class, 'profile']);
        Route::put('/profile', [SettingsController::class, 'updateProfile']);
        Route::get('/preferences', [SettingsController::class, 'preferences']);
        Route::put('/preferences', [SettingsController::class, 'updatePreferences']);
        Route::post('/avatar', [SettingsController::class, 'uploadAvatar']);
        Route::post('/change-password', [SettingsController::class, 'changePassword']);
        Route::post('/export-data', [SettingsController::class, 'exportData']);
        Route::delete('/account', [SettingsController::class, 'deleteAccount']);
    });
    
    // AI Coach routes (Phase 2)
    Route::prefix('ai-coach')->group(function () {
        Route::post('/chat', [AiCoachController::class, 'chat']);
        Route::get('/history', [AiCoachController::class, 'history']);
    });
    
    // File upload routes
    Route::post('/files/upload', [FileUploadController::class, 'upload']);
});

