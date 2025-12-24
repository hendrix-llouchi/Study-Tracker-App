<?php

namespace App\Http\Controllers;

use App\Http\Requests\Course\CreateCourseRequest;
use App\Http\Requests\Course\UpdateCourseRequest;
use App\Http\Traits\ApiResponse;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    use ApiResponse;

    /**
     * Get all user courses.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Course::where('user_id', $request->user()->id);

        if ($request->has('semester')) {
            $query->where('semester', $request->semester);
        }

        if ($request->has('active')) {
            $query->where('is_active', filter_var($request->active, FILTER_VALIDATE_BOOLEAN));
        }

        $courses = $query->get();

        return $this->successResponse(['courses' => $courses]);
    }

    /**
     * Create a new course.
     */
    public function store(CreateCourseRequest $request): JsonResponse
    {
        $course = Course::create([
            'user_id' => $request->user()->id,
            ...$request->validated(),
        ]);

        return $this->successResponse(['course' => $course], 'Course created successfully', 201);
    }

    /**
     * Get single course details.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $course = Course::where('user_id', $request->user()->id)
            ->with(['academicResults', 'studyPlans'])
            ->findOrFail($id);

        // Calculate statistics
        $statistics = [
            'total_study_hours' => $course->studySessions()->sum('duration') / 60,
            'average_grade' => $course->academicResults()->avg('percentage'),
            'results_count' => $course->academicResults()->count(),
        ];

        $course->statistics = $statistics;

        return $this->successResponse(['course' => $course]);
    }

    /**
     * Update course.
     */
    public function update(UpdateCourseRequest $request, string $id): JsonResponse
    {
        $course = Course::where('user_id', $request->user()->id)->findOrFail($id);
        $course->update($request->validated());

        return $this->successResponse(['course' => $course], 'Course updated successfully');
    }

    /**
     * Delete course.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $course = Course::where('user_id', $request->user()->id)->findOrFail($id);
        $course->delete();

        return $this->successResponse(null, 'Course deleted successfully');
    }
}

