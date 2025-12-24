<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponse;
use App\Services\WeeklyReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected WeeklyReportService $reportService
    ) {}

    /**
     * Get weekly progress report.
     */
    public function weekly(Request $request): JsonResponse
    {
        $weekStart = $request->input('week_start', now()->startOfWeek()->toDateString());
        $report = $this->reportService->getWeeklyReport($request->user(), $weekStart);

        return $this->successResponse(['report' => $report]);
    }

    /**
     * Generate weekly report manually.
     */
    public function generateWeekly(Request $request): JsonResponse
    {
        $request->validate([
            'week_start' => 'required|date',
        ]);

        $report = $this->reportService->generateWeeklyReport($request->user(), $request->week_start);

        return $this->successResponse(['report' => $report], 'Weekly report generated successfully', 201);
    }

    /**
     * Get advanced analytics.
     */
    public function analytics(Request $request): JsonResponse
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $groupBy = $request->input('group_by', 'week');

        $analytics = $this->reportService->getAnalytics($request->user(), $fromDate, $toDate, $groupBy);

        return $this->successResponse(['analytics' => $analytics]);
    }
}

