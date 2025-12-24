<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponse;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * Get dashboard statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $stats = $this->dashboardService->getStats($request->user());

        return $this->successResponse($stats);
    }

    /**
     * Get upcoming classes.
     */
    public function upcomingClasses(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 5);
        $days = $request->input('days', 7);

        $classes = $this->dashboardService->getUpcomingClasses($request->user(), $limit, $days);

        return $this->successResponse(['classes' => $classes]);
    }

    /**
     * Get study streak information.
     */
    public function studyStreak(Request $request): JsonResponse
    {
        $streak = $this->dashboardService->getStudyStreak($request->user());

        return $this->successResponse($streak);
    }

    /**
     * Get recent activities.
     */
    public function activities(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $activities = $this->dashboardService->getRecentActivities($request->user(), $limit);

        return $this->successResponse(['activities' => $activities]);
    }
}

