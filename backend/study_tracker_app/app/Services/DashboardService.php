<?php

namespace App\Services;

use App\Models\User;
use App\Models\StudySession;
use App\Models\TimetableClass;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    /**
     * Get dashboard statistics.
     */
    public function getStats(User $user): array
    {
        return Cache::remember("dashboard_stats_{$user->id}", 300, function () use ($user) {
            $courses = $user->courses()->where('is_active', true)->count();
            $totalHours = StudySession::where('user_id', $user->id)
                ->whereNotNull('duration')
                ->sum('duration') / 60;

            $plans = $user->studyPlans()
                ->where('date', '>=', now()->subDays(30))
                ->get();

            $completed = $plans->where('status', 'completed')->count();
            $total = $plans->count();
            $completionRate = $total > 0 ? ($completed / $total) * 100 : 0;

            $assignmentsDue = $user->assignments()
                ->where('status', '!=', 'completed')
                ->where('due_date', '>=', now())
                ->count();

            $gpa = $this->calculateGpa($user);

            return [
                'overall_performance' => round($gpa['current'] * 20, 0), // Convert to percentage
                'courses_enrolled' => $courses,
                'hours_studied' => round($totalHours, 1),
                'completion_rate' => round($completionRate, 0),
                'assignments_due' => $assignmentsDue,
                'gpa' => $gpa,
            ];
        });
    }

    /**
     * Get upcoming classes.
     */
    public function getUpcomingClasses(User $user, int $limit = 5, int $days = 7): array
    {
        $timetable = $user->timetables()->where('is_active', true)->first();
        
        if (!$timetable) {
            return [];
        }

        $classes = [];
        $today = now();
        $endDate = $today->copy()->addDays($days);

        for ($date = $today->copy(); $date <= $endDate; $date->addDay()) {
            $dayOfWeek = $date->dayOfWeek;
            
            $dayClasses = TimetableClass::where('timetable_id', $timetable->id)
                ->where('day_of_week', $dayOfWeek)
                ->with('course')
                ->get();

            foreach ($dayClasses as $class) {
                $classTime = Carbon::parse($date->format('Y-m-d') . ' ' . $class->start_time);
                
                if ($classTime >= $today) {
                    $classes[] = [
                        'id' => $class->id,
                        'course_name' => $class->course->name,
                        'course_code' => $class->course->code,
                        'instructor' => $class->instructor,
                        'date' => $date->format('Y-m-d'),
                        'start_time' => $class->start_time,
                        'end_time' => $class->end_time,
                        'location' => $class->location,
                        'type' => $class->class_type,
                    ];
                }
            }
        }

        usort($classes, function ($a, $b) {
            return strtotime($a['date'] . ' ' . $a['start_time']) - strtotime($b['date'] . ' ' . $b['start_time']);
        });

        return array_slice($classes, 0, $limit);
    }

    /**
     * Get study streak information.
     */
    public function getStudyStreak(User $user): array
    {
        $sessions = StudySession::where('user_id', $user->id)
            ->whereNotNull('end_time')
            ->orderBy('start_time', 'desc')
            ->get()
            ->groupBy(function ($session) {
                return Carbon::parse($session->start_time)->format('Y-m-d');
            })
            ->keys()
            ->toArray();

        $currentStreak = 0;
        $longestStreak = 0;
        $tempStreak = 0;
        $today = now()->format('Y-m-d');
        $checkDate = Carbon::parse($today);

        // Calculate current streak
        while (in_array($checkDate->format('Y-m-d'), $sessions)) {
            $currentStreak++;
            $checkDate->subDay();
        }

        // Calculate longest streak
        $prevDate = null;
        foreach ($sessions as $date) {
            $sessionDate = Carbon::parse($date);
            if ($prevDate && $sessionDate->diffInDays($prevDate) === 1) {
                $tempStreak++;
            } else {
                $tempStreak = 1;
            }
            $longestStreak = max($longestStreak, $tempStreak);
            $prevDate = $sessionDate;
        }

        // Get last 7 days
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $days[] = [
                'day' => $date->format('D'),
                'completed' => in_array($date->format('Y-m-d'), $sessions),
            ];
        }

        return [
            'current_streak' => $currentStreak,
            'longest_streak' => $longestStreak,
            'days' => $days,
        ];
    }

    /**
     * Get recent activities.
     */
    public function getRecentActivities(User $user, int $limit = 10): array
    {
        $activities = [];

        // Study plan completions
        $completedPlans = $user->studyPlans()
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->orderBy('completed_at', 'desc')
            ->limit($limit)
            ->with('course')
            ->get();

        foreach ($completedPlans as $plan) {
            $activities[] = [
                'id' => $plan->id,
                'type' => 'plan_completed',
                'description' => "Completed study session for {$plan->course->name}",
                'timestamp' => $plan->completed_at->toIso8601String(),
                'metadata' => [
                    'course' => $plan->course->code,
                    'duration' => $plan->actual_duration ?? $plan->planned_duration,
                ],
            ];
        }

        usort($activities, function ($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        return array_slice($activities, 0, $limit);
    }

    /**
     * Calculate GPA.
     */
    private function calculateGpa(User $user): array
    {
        $results = $user->academicResults()
            ->whereNotNull('percentage')
            ->get();

        if ($results->isEmpty()) {
            return [
                'current' => 0,
                'trend' => 'stable',
            ];
        }

        $currentGpa = $results->avg('percentage') / 20; // Convert to 4.0 scale

        return [
            'current' => round($currentGpa, 2),
            'trend' => 'improving', // TODO: Calculate actual trend
        ];
    }
}

