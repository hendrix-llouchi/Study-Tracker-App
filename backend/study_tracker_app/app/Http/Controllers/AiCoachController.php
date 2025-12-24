<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponse;
use App\Models\AiChatHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiCoachController extends Controller
{
    use ApiResponse;

    /**
     * Send message to AI coach.
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|uuid',
            'message' => 'required|string|max:5000',
        ]);

        // Save user message
        AiChatHistory::create([
            'user_id' => $request->user()->id,
            'session_id' => $request->session_id,
            'role' => 'user',
            'message' => $request->message,
        ]);

        // TODO: Integrate with Google Gemini API
        $response = "Based on your current performance, I recommend focusing on your study habits...";

        // Save assistant response
        AiChatHistory::create([
            'user_id' => $request->user()->id,
            'session_id' => $request->session_id,
            'role' => 'assistant',
            'message' => $response,
        ]);

        return $this->successResponse([
            'response' => $response,
            'session_id' => $request->session_id,
            'context_used' => [
                'course_performance' => true,
                'study_patterns' => true,
            ],
        ]);
    }

    /**
     * Get chat history.
     */
    public function history(Request $request): JsonResponse
    {
        $query = AiChatHistory::where('user_id', $request->user()->id);

        if ($request->has('session_id')) {
            $query->where('session_id', $request->session_id);
        }

        $history = $query->orderBy('created_at')->get();

        return $this->successResponse(['history' => $history]);
    }
}

