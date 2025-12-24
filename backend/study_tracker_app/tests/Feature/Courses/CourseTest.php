<?php

namespace Tests\Feature\Courses;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_course(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/courses', [
            'name' => 'Data Structures',
            'code' => 'CS201',
            'credits' => 4,
            'semester' => 'Fall 2024',
            'color' => 'blue',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['course'],
            ]);

        $this->assertDatabaseHas('courses', [
            'user_id' => $user->id,
            'code' => 'CS201',
        ]);
    }

    public function test_user_can_list_their_courses(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Course::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/v1/courses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['courses'],
            ]);

        $this->assertCount(3, $response->json('data.courses'));
    }
}

