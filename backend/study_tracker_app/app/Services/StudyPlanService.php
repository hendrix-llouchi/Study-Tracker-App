<?php

namespace App\Services;

use App\Models\User;
use App\Models\TimetableClass;
use Carbon\Carbon;

class StudyPlanService
{
    /**
     * Check for timetable conflicts.
     */
    public function checkConflicts(User $user, string $date, string $startTime, int $duration): array
    {
        $conflicts = [];
        $timetable = $user->timetables()->where('is_active', true)->first();

        if (!$timetable) {
            return $conflicts;
        }

        $planDate = Carbon::parse($date);
        $planStart = Carbon::parse($date . ' ' . $startTime);
        $planEnd = $planStart->copy()->addMinutes($duration);
        $dayOfWeek = $planDate->dayOfWeek;

        $classes = TimetableClass::where('timetable_id', $timetable->id)
            ->where('day_of_week', $dayOfWeek)
            ->with('course')
            ->get();

        foreach ($classes as $class) {
            $classStart = Carbon::parse($date . ' ' . $class->start_time);
            $classEnd = Carbon::parse($date . ' ' . $class->end_time);

            if ($planStart->lt($classEnd) && $planEnd->gt($classStart)) {
                $conflicts[] = [
                    'type' => 'class',
                    'course_name' => $class->course->name,
                    'start_time' => $class->start_time,
                    'end_time' => $class->end_time,
                    'location' => $class->location,
                ];
            }
        }

        return $conflicts;
    }
}

