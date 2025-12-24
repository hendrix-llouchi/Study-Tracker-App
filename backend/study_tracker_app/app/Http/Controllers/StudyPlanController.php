<?php

namespace App\Http\Controllers;

use App\Http\Requests\Planning\CreatePlanRequest;
use App\Http\Requests\Planning\UpdatePlanRequest;
use App\Http\Traits\ApiResponse;
use App\Models\StudyPlan;
use App\Services\StudyPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudyPlanController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected StudyPlanService $studyPlanService
    ) {}

    /**
     * Get study plans.
     */
    public function index(Request $request): JsonResponse
    {
        $query = StudyPlan::where('user_id', $request->user()->id)
            ->with('course');

        if ($request->has('date')) {
            $query->where('date', $request->date);
        } elseif ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('date', [$request->from_date, $request->to_date]);
        } else {
            $query->where('date', today());
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        $plans = $query->get();

        $summary = [
            'total_plans' => $plans->count(),
            'pending' => $plans->where('status', 'pending')->count(),
            'completed' => $plans->where('status', 'completed')->count(),
            'total_planned_minutes' => $plans->sum('planned_duration'),
        ];

        return $this->successResponse([
            'plans' => $plans,
            'summary' => $summary,
        ]);
    }

    /**
     * Create study plan.
     */
    public function store(CreatePlanRequest $request): JsonResponse
    {
        $conflicts = $this->studyPlanService->checkConflicts(
            $request->user(),
            $request->date,
            $request->start_time,
            $request->planned_duration
        );

        $plan = StudyPlan::create([
            'user_id' => $request->user()->id,
            ...$request->validated(),
        ]);

        $plan->load('course');

        return $this->successResponse([
            'plan' => $plan,
            'conflicts' => $conflicts,
        ], 'Study plan created successfully', 201);
    }

    /**
     * Get single plan.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $plan = StudyPlan::where('user_id', $request->user()->id)
            ->with('course')
            ->findOrFail($id);

        return $this->successResponse(['plan' => $plan]);
    }

    /**
     * Update study plan.
     */
    public function update(UpdatePlanRequest $request, string $id): JsonResponse
    {
        $plan = StudyPlan::where('user_id', $request->user()->id)->findOrFail($id);
        $plan->update($request->validated());
        $plan->load('course');

        return $this->successResponse(['plan' => $plan], 'Plan updated successfully');
    }

    /**
     * Delete study plan.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $plan = StudyPlan::where('user_id', $request->user()->id)->findOrFail($id);
        $plan->delete();

        return $this->successResponse(null, 'Plan deleted successfully');
    }

    /**
     * Mark plan as completed.
     */
    public function complete(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'actual_duration' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $plan = StudyPlan::where('user_id', $request->user()->id)->findOrFail($id);
        $plan->update([
            'status' => 'completed',
            'completed_at' => now(),
            'actual_duration' => $request->actual_duration ?? $plan->planned_duration,
            'notes' => $request->notes ?? $plan->notes,
        ]);

        return $this->successResponse(['plan' => $plan], 'Plan marked as completed');
    }

    /**
     * Check for timetable conflicts.
     */
    public function checkConflicts(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'duration' => 'required|integer|min:1',
        ]);

        $conflicts = $this->studyPlanService->checkConflicts(
            $request->user(),
            $request->date,
            $request->start_time,
            $request->duration
        );

        return $this->successResponse([
            'has_conflicts' => count($conflicts) > 0,
            'conflicts' => $conflicts,
        ]);
    }
}

