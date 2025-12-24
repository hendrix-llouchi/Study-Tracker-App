<?php

namespace App\Http\Controllers;

use App\Http\Requests\Assignment\CreateAssignmentRequest;
use App\Http\Requests\Assignment\UpdateAssignmentRequest;
use App\Http\Traits\ApiResponse;
use App\Models\Assignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    use ApiResponse;

    /**
     * Get all assignments.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Assignment::where('user_id', $request->user()->id)
            ->with('course');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->boolean('upcoming')) {
            $query->where('due_date', '>=', now())
                ->where('status', '!=', 'completed');
        }

        $assignments = $query->orderBy('due_date')->get();

        // Add days_until_due
        $assignments->transform(function ($assignment) {
            $assignment->days_until_due = now()->diffInDays($assignment->due_date, false);
            return $assignment;
        });

        return $this->successResponse(['assignments' => $assignments]);
    }

    /**
     * Create assignment.
     */
    public function store(CreateAssignmentRequest $request): JsonResponse
    {
        $assignment = Assignment::create([
            'user_id' => $request->user()->id,
            ...$request->validated(),
        ]);

        $assignment->load('course');

        return $this->successResponse(['assignment' => $assignment], 'Assignment created successfully', 201);
    }

    /**
     * Get single assignment.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $assignment = Assignment::where('user_id', $request->user()->id)
            ->with('course')
            ->findOrFail($id);

        return $this->successResponse(['assignment' => $assignment]);
    }

    /**
     * Update assignment.
     */
    public function update(UpdateAssignmentRequest $request, string $id): JsonResponse
    {
        $assignment = Assignment::where('user_id', $request->user()->id)->findOrFail($id);
        $assignment->update($request->validated());
        $assignment->load('course');

        return $this->successResponse(['assignment' => $assignment], 'Assignment updated successfully');
    }

    /**
     * Delete assignment.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $assignment = Assignment::where('user_id', $request->user()->id)->findOrFail($id);
        $assignment->delete();

        return $this->successResponse(null, 'Assignment deleted successfully');
    }

    /**
     * Mark assignment as completed.
     */
    public function complete(Request $request, string $id): JsonResponse
    {
        $assignment = Assignment::where('user_id', $request->user()->id)->findOrFail($id);
        $assignment->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return $this->successResponse(['assignment' => $assignment], 'Assignment marked as completed');
    }
}

