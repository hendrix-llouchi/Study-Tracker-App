<?php

namespace Database\Seeders;

use App\Models\AcademicResult;
use App\Models\Course;
use App\Models\StudyPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AcademicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Primary Test User with specific email
        $testUser = User::firstOrCreate(
            ['email' => 'test@studytracker.com'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'university' => 'Test University',
                'semester' => 'Fall 2024',
            ]
        );

        // Create 5 random Courses for the test user
        $courses = Course::factory()
            ->count(5)
            ->create([
                'user_id' => $testUser->id,
            ]);

        // Generate 10 Study Plans linked to those courses
        $studyPlans = [];
        for ($i = 0; $i < 10; $i++) {
            $studyPlans[] = StudyPlan::factory()->create([
                'user_id' => $testUser->id,
                'course_id' => $courses->random()->id,
            ]);
        }

        // Generate 5 Academic Results linked to those courses
        $academicResults = [];
        for ($i = 0; $i < 5; $i++) {
            $academicResults[] = AcademicResult::factory()->create([
                'user_id' => $testUser->id,
                'course_id' => $courses->random()->id,
            ]);
        }

        $this->command->info('✓ Created test user: ' . $testUser->email);
        $this->command->info('✓ Created ' . $courses->count() . ' courses');
        $this->command->info('✓ Created ' . count($studyPlans) . ' study plans');
        $this->command->info('✓ Created ' . count($academicResults) . ' academic results');
    }
}

