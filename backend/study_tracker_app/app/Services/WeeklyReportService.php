<?php

namespace App\Services;

use App\Models\User;
use App\Models\WeeklyReport;
use App\Models\StudyPlan;
use App\Models\StudySession;
use Carbon\Carbon;

class WeeklyReportService
{
    /**
     * Get weekly report.
     */
    public function getWeeklyReport(User $user, string $weekStart): array
    {
        $weekStartDate = Carbon::parse($weekStart);
        $weekEndDate = $weekStartDate->copy()->endOfWeek();

        $report = WeeklyReport::where('user_id', $user->id)
            ->where('week_start_date', $weekStartDate->format('Y-m-d'))
            ->first();

        if (!$report) {
            $report = $this->generateWeeklyReport($user, $weekStart);
        }

        return [
            'id' => $report->id,
            'week_start_date' => $report->week_start_date->format('Y-m-d'),
            'week_end_date' => $report->week_end_date->format('Y-m-d'),
            'total_study_hours' => $report->total_study_hours,
            'planned_hours' => $report->planned_hours,
            'completion_rate' => $report->completion_rate,
            'most_studied_course' => $report->mostStudiedCourse ? [
                'id' => $report->mostStudiedCourse->id,
                'name' => $report->mostStudiedCourse->name,
                'hours' => $report->total_study_hours, // Simplified
            ] : null,
            'least_studied_course' => $report->leastStudiedCourse ? [
                'id' => $report->leastStudiedCourse->id,
                'name' => $report->leastStudiedCourse->name,
            ] : null,
            'performance_trend' => $report->performance_trend,
            'daily_breakdown' => $this->getDailyBreakdown($user, $weekStartDate, $weekEndDate),
            'ai_insights' => $report->ai_insights,
            'generated_at' => ($report->generated_at ?? $report->created_at ?? now())->toIso8601String(),
        ];
    }

    /**
     * Generate weekly report.
     */
    public function generateWeeklyReport(User $user, string $weekStart): WeeklyReport
    {
        $weekStartDate = Carbon::parse($weekStart);
        $weekEndDate = $weekStartDate->copy()->endOfWeek();

        // Calculate study hours
        $sessions = StudySession::where('user_id', $user->id)
            ->whereBetween('start_time', [$weekStartDate, $weekEndDate])
            ->whereNotNull('duration')
            ->get();

        $totalHours = $sessions->sum('duration') / 60;

        // Calculate planned hours
        $plans = StudyPlan::where('user_id', $user->id)
            ->whereBetween('date', [$weekStartDate->format('Y-m-d'), $weekEndDate->format('Y-m-d')])
            ->get();

        $plannedHours = $plans->sum('planned_duration') / 60;
        $completedPlans = $plans->where('status', 'completed')->count();
        $completionRate = $plans->count() > 0 ? ($completedPlans / $plans->count()) * 100 : 0;

        // Find most/least studied courses
        $courseHours = $sessions->groupBy('course_id')->map(function ($courseSessions) {
            return $courseSessions->sum('duration') / 60;
        });

        $mostStudiedCourseId = $courseHours->sortDesc()->keys()->first();
        $leastStudiedCourseId = $courseHours->sort()->keys()->first();

        $report = WeeklyReport::updateOrCreate(
            [
                'user_id' => $user->id,
                'week_start_date' => $weekStartDate->format('Y-m-d'),
            ],
            [
                'week_end_date' => $weekEndDate->format('Y-m-d'),
                'total_study_hours' => round($totalHours, 2),
                'planned_hours' => round($plannedHours, 2),
                'completion_rate' => round($completionRate, 2),
                'most_studied_course_id' => $mostStudiedCourseId,
                'least_studied_course_id' => $leastStudiedCourseId,
                'performance_trend' => $this->calculatePerformanceTrend($user, $weekStartDate),
                'ai_insights' => $this->generateAiInsights($user, $totalHours, $completionRate),
            ]
        );

        // Set generated_at if the report was just created or if it's null (for existing records)
        if ($report->wasRecentlyCreated || !$report->generated_at) {
            $report->generated_at = now();
            $report->save();
        }

        return $report;
    }

    /**
     * Get analytics.
     */
    public function getAnalytics(User $user, ?string $fromDate = null, ?string $toDate = null, string $groupBy = 'week'): array
    {
        $fromDate = $fromDate ? Carbon::parse($fromDate) : now()->subDays(60);
        $toDate = $toDate ? Carbon::parse($toDate) : now();

        $sessions = StudySession::where('user_id', $user->id)
            ->whereBetween('start_time', [$fromDate, $toDate])
            ->whereNotNull('duration')
            ->get();

        $totalDays = $fromDate->diffInDays($toDate);
        $daysStudied = $sessions->groupBy(function ($session) {
            return Carbon::parse($session->start_time)->format('Y-m-d');
        })->count();

        $consistencyRate = $totalDays > 0 ? ($daysStudied / $totalDays) * 100 : 0;

        // Time distribution by course
        $timeDistribution = $sessions->groupBy('course_id')->map(function ($courseSessions, $courseId) {
            $course = $courseSessions->first()->course;
            $hours = $courseSessions->sum('duration') / 60;
            return [
                'course_id' => $courseId,
                'course_name' => $course->name ?? 'Unknown',
                'hours' => round($hours, 1),
            ];
        })->values()->toArray();

        $totalHours = array_sum(array_column($timeDistribution, 'hours'));
        foreach ($timeDistribution as &$dist) {
            $dist['percentage'] = $totalHours > 0 ? round(($dist['hours'] / $totalHours) * 100, 1) : 0;
        }

        return [
            'study_consistency' => [
                'days_studied' => $daysStudied,
                'total_days' => $totalDays,
                'consistency_rate' => round($consistencyRate, 1),
            ],
            'time_distribution' => $timeDistribution,
            'productivity_trends' => [], // TODO: Implement
            'weak_areas' => [], // TODO: Implement
        ];
    }

    /**
     * Get daily breakdown.
     */
    private function getDailyBreakdown(User $user, Carbon $weekStart, Carbon $weekEnd): array
    {
        $breakdown = [];
        $current = $weekStart->copy();

        while ($current <= $weekEnd) {
            $dateStr = $current->format('Y-m-d');
            
            $plans = StudyPlan::where('user_id', $user->id)
                ->where('date', $dateStr)
                ->get();

            $sessions = StudySession::where('user_id', $user->id)
                ->whereDate('start_time', $dateStr)
                ->whereNotNull('duration')
                ->get();

            $planned = $plans->sum('planned_duration') / 60;
            $actual = $sessions->sum('duration') / 60;
            $completionRate = $planned > 0 ? ($actual / $planned) * 100 : 0;

            $breakdown[] = [
                'date' => $dateStr,
                'planned' => round($planned, 1),
                'actual' => round($actual, 1),
                'completion_rate' => round($completionRate, 1),
            ];

            $current->addDay();
        }

        return $breakdown;
    }

    /**
     * Calculate performance trend.
     */
    private function calculatePerformanceTrend(User $user, Carbon $weekStart): string
    {
        // Simplified - compare with previous week
        $previousWeek = $weekStart->copy()->subWeek();
        $previousReport = WeeklyReport::where('user_id', $user->id)
            ->where('week_start_date', $previousWeek->format('Y-m-d'))
            ->first();

        if (!$previousReport) {
            return 'stable';
        }

        $currentReport = WeeklyReport::where('user_id', $user->id)
            ->where('week_start_date', $weekStart->format('Y-m-d'))
            ->first();

        if (!$currentReport) {
            return 'stable';
        }

        $diff = $currentReport->completion_rate - $previousReport->completion_rate;

        if ($diff > 5) {
            return 'improving';
        } elseif ($diff < -5) {
            return 'declining';
        }

        return 'stable';
    }

    /**
     * Generate AI insights.
     */
    private function generateAiInsights(User $user, float $totalHours, float $completionRate): string
    {
        if ($completionRate >= 80) {
            return "Great progress this week! You've maintained excellent consistency.";
        } elseif ($completionRate >= 60) {
            return "Good effort this week. Try to increase your study time for better results.";
        } else {
            return "This week's completion rate is below target. Focus on creating a more consistent study schedule.";
        }
    }
}

