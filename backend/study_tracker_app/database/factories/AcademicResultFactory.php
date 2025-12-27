<?php

namespace Database\Factories;

use App\Models\AcademicResult;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AcademicResult>
 */
class AcademicResultFactory extends Factory
{
    protected $model = AcademicResult::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $score = fake()->numberBetween(40, 100);
        $maxScore = 100.00;
        $percentage = ($score / $maxScore) * 100;
        
        // Calculate grade based on percentage
        $grade = match (true) {
            $percentage >= 90 => 'A',
            $percentage >= 80 => 'B',
            $percentage >= 70 => 'C',
            $percentage >= 60 => 'D',
            default => 'F',
        };

        $assessmentTypes = ['quiz', 'midterm', 'final', 'assignment', 'project', 'lab', 'homework'];
        $assessmentType = fake()->randomElement($assessmentTypes);
        
        $assessmentNames = [
            'quiz' => ['Quiz 1', 'Quiz 2', 'Weekly Quiz', 'Chapter Quiz'],
            'midterm' => ['Midterm Exam', 'Midterm 1', 'Midterm 2'],
            'final' => ['Final Exam', 'Final Assessment'],
            'assignment' => ['Assignment 1', 'Assignment 2', 'Homework Assignment'],
            'project' => ['Final Project', 'Group Project', 'Research Project'],
            'lab' => ['Lab Report 1', 'Lab Exercise', 'Lab Assignment'],
            'homework' => ['Homework 1', 'Homework 2', 'Weekly Homework'],
        ];

        return [
            'id' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'assessment_type' => $assessmentType,
            'assessment_name' => fake()->randomElement($assessmentNames[$assessmentType] ?? ['Assessment']),
            'score' => (string) $score,
            'max_score' => (string) $maxScore,
            'percentage' => (string) round($percentage, 2),
            'grade' => $grade,
            'weight' => fake()->randomFloat(2, 5, 30), // 5% to 30% weight
            'semester' => fake()->randomElement(['Fall 2024', 'Spring 2024', 'Summer 2024', 'Fall 2025', 'Spring 2025']),
            'date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}

