<?php

namespace App\Services;

use App\Models\User;
use App\Models\AcademicResult;
use Illuminate\Support\Collection;

class GpaCalculationService
{
    /**
     * Get GPA trend over time.
     */
    public function getGpaTrend(User $user, string $period = 'all'): array
    {
        $query = AcademicResult::where('user_id', $user->id)
            ->whereNotNull('percentage')
            ->orderBy('date');

        if ($period === 'semester') {
            $results = $query->get()->groupBy('semester');
        } elseif ($period === 'year') {
            $results = $query->get()->groupBy(function ($result) {
                return substr($result->semester, -4); // Extract year
            });
        } else {
            $results = $query->get()->groupBy('semester');
        }

        $trend = [];
        foreach ($results as $periodName => $periodResults) {
            $avgPercentage = $periodResults->avg('percentage');
            $gpa = $avgPercentage / 20; // Convert to 4.0 scale

            $trend[] = [
                'period' => $periodName,
                'gpa' => round($gpa, 2),
                'credits' => $periodResults->sum(function ($result) {
                    return $result->course->credits ?? 0;
                }),
            ];
        }

        $currentGpa = $trend[count($trend) - 1]['gpa'] ?? 0;
        $cumulativeGpa = $this->calculateCumulativeGpa($user);

        return [
            'trend' => $trend,
            'current_gpa' => $currentGpa,
            'cumulative_gpa' => $cumulativeGpa,
            'trend_direction' => $this->determineTrend($trend),
        ];
    }

    /**
     * Get subject-wise performance.
     */
    public function getSubjectPerformance(User $user, ?string $semester = null): array
    {
        $query = AcademicResult::where('user_id', $user->id)
            ->with('course');

        if ($semester) {
            $query->where('semester', $semester);
        }

        $results = $query->get()->groupBy('course_id');

        $subjects = [];
        foreach ($results as $courseId => $courseResults) {
            $course = $courseResults->first()->course;
            $avgScore = $courseResults->avg('percentage');

            $subjects[] = [
                'course_id' => $courseId,
                'course_name' => $course->name,
                'average_score' => round($avgScore, 1),
                'total_assessments' => $courseResults->count(),
                'trend' => $this->calculateCourseTrend($courseResults),
            ];
        }

        return $subjects;
    }

    /**
     * Calculate cumulative GPA.
     */
    private function calculateCumulativeGpa(User $user): float
    {
        $results = AcademicResult::where('user_id', $user->id)
            ->whereNotNull('percentage')
            ->with('course')
            ->get();

        if ($results->isEmpty()) {
            return 0;
        }

        $totalPoints = 0;
        $totalCredits = 0;

        foreach ($results as $result) {
            $credits = $result->course->credits ?? 3;
            $gpaPoints = ($result->percentage / 20) * $credits;
            $totalPoints += $gpaPoints;
            $totalCredits += $credits;
        }

        return $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0;
    }

    /**
     * Determine trend direction.
     */
    private function determineTrend(array $trend): string
    {
        if (count($trend) < 2) {
            return 'stable';
        }

        $lastTwo = array_slice($trend, -2);
        $diff = $lastTwo[1]['gpa'] - $lastTwo[0]['gpa'];

        if ($diff > 0.1) {
            return 'improving';
        } elseif ($diff < -0.1) {
            return 'declining';
        }

        return 'stable';
    }

    /**
     * Calculate course trend.
     */
    private function calculateCourseTrend(Collection $results): string
    {
        if ($results->count() < 2) {
            return 'stable';
        }

        $sorted = $results->sortBy('date')->values();
        $first = $sorted->first()->percentage;
        $last = $sorted->last()->percentage;

        if ($last > $first + 5) {
            return 'improving';
        } elseif ($last < $first - 5) {
            return 'declining';
        }

        return 'stable';
    }
}

