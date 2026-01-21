<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\StudyPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudyPlan>
 */
class StudyPlanFactory extends Factory
{
    protected $model = StudyPlan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate dates for the current week or past
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();
        $status = fake()->randomElement(['completed', 'pending', 'in-progress', 'missed']);
        
        // If completed, the date must be in the past or today
        if ($status === 'completed') {
            $randomDate = fake()->dateTimeBetween($startOfWeek, 'now');
        } else {
            $randomDate = fake()->dateTimeBetween($startOfWeek, $endOfWeek);
        }
        
        $plannedDuration = fake()->numberBetween(30, 180); // minutes
        
        return [
            'id' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'topic' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'date' => $randomDate->format('Y-m-d'),
            'start_time' => fake()->time('H:i'),
            'planned_duration' => $plannedDuration,
            'actual_duration' => $status === 'completed' ? fake()->numberBetween($plannedDuration - 30, $plannedDuration + 60) : null,
            'priority' => fake()->randomElement(['high', 'medium', 'low']),
            'study_type' => fake()->randomElement(['review', 'new-material', 'practice']),
            'status' => $status,
            'completed_at' => $status === 'completed' ? fake()->dateTimeBetween($randomDate, 'now') : null,
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}

