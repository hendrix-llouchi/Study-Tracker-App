<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponse;
use App\Models\AiChatHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiCoachController extends Controller
{
    use ApiResponse;

    /**
     * The system prompt defines the "Balanced Mentor" persona:
     * A blend of a supportive peer and a disciplined professor.
     */
    protected string $systemPrompt = "You are the STRAP (Study Tracker App) AI Coach. Your persona is that of a 'Balanced Mentor' - a perfect blend of a supportive, friendly peer and a disciplined, authoritative professor.
    
    1. Tone: Warm, encouraging, but firm about academic standards and deadlines.
    2. Style: Use structured advice (bullet points, clear steps) but keep the conversational flow natural.
    3. Motivation: Focus on long-term growth and study-life balance.
    4. Feedback: Celebrate wins (friendly), but don't shy away from pointing out areas of procrastination or poor performance (disciplined).
    
    You have access to the student's current grades and study plans. Use this context to give specific, data-driven advice.";

    /**
     * Send message to AI coach.
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|uuid',
            'message' => 'required|string|max:5000',
        ]);

        $user = $request->user();

        // Save user message
        AiChatHistory::create([
            'user_id' => $user->id,
            'session_id' => $request->session_id,
            'role' => 'user',
            'message' => $request->message,
        ]);

        // Placeholder for Gemini API Integration
        // In a real implementation, we would send the systemPrompt + history + user message to Gemini.

        $response = $this->generatePlaceholderResponse($request->message);

        // Save assistant response
        AiChatHistory::create([
            'user_id' => $user->id,
            'session_id' => $request->session_id,
            'role' => 'assistant',
            'message' => $response,
        ]);

        return $this->successResponse([
            'response' => $response,
            'session_id' => $request->session_id,
            'persona' => 'Balanced Mentor',
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

    /**
     * Generate a response that reflects the "Balanced Mentor" persona.
     */
    private function generatePlaceholderResponse(string $userMessage): string
    {
        $lowerMessage = strtolower($userMessage);

        if (str_contains($lowerMessage, 'gpa') || str_contains($lowerMessage, 'performance')) {
            return "I've analyzed your performance data. You've got some solid scores in most areas, but I noticed a slight dip in your recent assessments. As your mentor, I'm proud of your effort, but as your professor, I must insist we tighten up your review schedule before the finals. What's your plan for this weekend?";
        }

        if (str_contains($lowerMessage, 'tired') || str_contains($lowerMessage, 'break') || str_contains($lowerMessage, 'stress')) {
            return "I hear you. Balancing everything is tough, and it's okay to feel drained. However, remember that discipline is choosing between what you want now and what you want most. Take a 20-minute power break, then let's tackle just one focused task. You've got this.";
        }

        return "That's an interesting perspective. Let's look at how we can apply that to your current study goals. Remember, consistency is the hallmark of every successful scholar. How can I help you stay on track today?";
    }
}
