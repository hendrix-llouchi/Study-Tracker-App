<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'code' => strtoupper(fake()->bothify('??###')),
            'credits' => fake()->numberBetween(1, 6),
            'instructor' => fake()->name(),
            'color' => fake()->randomElement(['blue', 'green', 'red', 'yellow', 'purple', 'orange']),
            'semester' => 'Fall 2024',
            'academic_year' => '2024-2025',
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}

