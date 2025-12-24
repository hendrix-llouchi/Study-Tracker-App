<?php

namespace App\Http\Controllers;

use App\Http\Requests\Performance\CreateResultRequest;
use App\Http\Requests\Performance\UpdateResultRequest;
use App\Http\Traits\ApiResponse;
use App\Models\AcademicResult;
use App\Services\GpaCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected GpaCalculationService $gpaService
    ) {}

    /**
     * Get all academic results.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AcademicResult::where('user_id', $request->user()->id)
            ->with('course');

        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->has('semester')) {
            $query->where('semester', $request->semester);
        }

        if ($request->has('from_date')) {
            $query->where('date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('date', '<=', $request->to_date);
        }

        if ($request->has('assessment_type')) {
            $query->where('assessment_type', $request->assessment_type);
        }

        $results = $query->paginate($request->input('per_page', 10));

        return $this->successResponse([
            'results' => $results->items(),
            'pagination' => [
                'total' => $results->total(),
                'per_page' => $results->perPage(),
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
            ],
        ]);
    }

    /**
     * Add new academic result.
     */
    public function store(CreateResultRequest $request): JsonResponse
    {
        $result = AcademicResult::create([
            'user_id' => $request->user()->id,
            ...$request->validated(),
        ]);

        $result->load('course');

        return $this->successResponse(['result' => $result], 'Result added successfully', 201);
    }

    /**
     * Bulk upload results (CSV).
     */
    public function bulkUpload(Request $request): JsonResponse
    {
        // TODO: Implement CSV parsing and bulk upload
        return $this->successResponse([
            'total_uploaded' => 0,
            'successful' => 0,
            'failed' => 0,
            'errors' => [],
        ], 'Bulk upload not yet implemented', 201);
    }

    /**
     * Get single result.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $result = AcademicResult::where('user_id', $request->user()->id)
            ->with('course')
            ->findOrFail($id);

        return $this->successResponse(['result' => $result]);
    }

    /**
     * Update result.
     */
    public function update(UpdateResultRequest $request, string $id): JsonResponse
    {
        $result = AcademicResult::where('user_id', $request->user()->id)->findOrFail($id);
        $result->update($request->validated());
        $result->load('course');

        return $this->successResponse(['result' => $result], 'Result updated successfully');
    }

    /**
     * Delete result.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $result = AcademicResult::where('user_id', $request->user()->id)->findOrFail($id);
        $result->delete();

        return $this->successResponse(null, 'Result deleted successfully');
    }

    /**
     * Get GPA trend over time.
     */
    public function gpaTrend(Request $request): JsonResponse
    {
        $period = $request->input('period', 'all');
        $trend = $this->gpaService->getGpaTrend($request->user(), $period);

        return $this->successResponse($trend);
    }

    /**
     * Get subject-wise performance.
     */
    public function subjects(Request $request): JsonResponse
    {
        $semester = $request->input('semester');
        $subjects = $this->gpaService->getSubjectPerformance($request->user(), $semester);

        return $this->successResponse(['subjects' => $subjects]);
    }
}

