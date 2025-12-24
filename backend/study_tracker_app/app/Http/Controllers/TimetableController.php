<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponse;
use App\Models\Timetable;
use App\Models\TimetableClass;
use App\Services\TimetableOcrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected TimetableOcrService $ocrService
    ) {}

    /**
     * Get user timetable.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Timetable::where('user_id', $request->user()->id)
            ->with(['classes.course']);

        if ($request->has('semester')) {
            $query->where('semester', $request->semester);
        } else {
            $query->where('is_active', true);
        }

        $timetable = $query->first();

        if (!$timetable) {
            return $this->successResponse(['timetable' => null]);
        }

        return $this->successResponse(['timetable' => $timetable]);
    }

    /**
     * Create or update timetable.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'semester' => 'required|string|max:100',
            'academic_year' => 'nullable|string|max:20',
            'classes' => 'required|array',
            'classes.*.course_id' => 'required|uuid|exists:courses,id',
            'classes.*.day_of_week' => 'required|integer|between:0,6',
            'classes.*.start_time' => 'required|date_format:H:i',
            'classes.*.end_time' => 'required|date_format:H:i|after:classes.*.start_time',
            'classes.*.location' => 'nullable|string|max:255',
            'classes.*.class_type' => 'nullable|string|in:lecture,lab,tutorial,seminar',
            'classes.*.instructor' => 'nullable|string|max:255',
        ]);

        $timetable = Timetable::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'semester' => $request->semester,
                'academic_year' => $request->academic_year,
            ],
            [
                'is_active' => true,
            ]
        );

        // Delete existing classes
        $timetable->classes()->delete();

        // Create new classes
        foreach ($request->classes as $classData) {
            TimetableClass::create([
                'timetable_id' => $timetable->id,
                'course_id' => $classData['course_id'],
                'day_of_week' => $classData['day_of_week'],
                'start_time' => $classData['start_time'],
                'end_time' => $classData['end_time'],
                'location' => $classData['location'] ?? null,
                'class_type' => $classData['class_type'] ?? 'lecture',
                'instructor' => $classData['instructor'] ?? null,
            ]);
        }

        $timetable->load(['classes.course']);

        return $this->successResponse(['timetable' => $timetable], 'Timetable created successfully', 201);
    }

    /**
     * Upload timetable (image/PDF with OCR).
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'semester' => 'required|string|max:100',
        ]);

        $file = $request->file('file');
        $parsedClasses = $this->ocrService->processTimetable($file);

        // TODO: Create timetable and classes from parsed data
        // For now, return parsed data for manual review

        return $this->successResponse([
            'parsed_classes' => $parsedClasses,
            'requires_manual_review' => true,
        ], 'Timetable uploaded and processed', 201);
    }

    /**
     * Update timetable class.
     */
    public function updateClass(Request $request, string $id): JsonResponse
    {
        $class = TimetableClass::whereHas('timetable', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->findOrFail($id);

        $request->validate([
            'location' => 'nullable|string|max:255',
            'instructor' => 'nullable|string|max:255',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
        ]);

        $class->update($request->only(['location', 'instructor', 'start_time', 'end_time']));
        $class->load('course');

        return $this->successResponse(['class' => $class], 'Class updated successfully');
    }

    /**
     * Delete timetable class.
     */
    public function deleteClass(Request $request, string $id): JsonResponse
    {
        $class = TimetableClass::whereHas('timetable', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->findOrFail($id);

        $class->delete();

        return $this->successResponse(null, 'Class deleted successfully');
    }
}

