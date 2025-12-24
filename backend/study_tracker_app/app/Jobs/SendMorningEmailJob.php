<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMorningEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1 min, 5 min, 15 min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user
    ) {}

    /**
     * Execute the job.
     */
    public function handle(EmailService $emailService): void
    {
        try {
            $preferences = $this->user->preferences;
            
            if (!$preferences || !$preferences->email_notifications) {
                return;
            }

            // Get today's study plans
            $plans = $this->user->studyPlans()
                ->where('date', today())
                ->where('status', '!=', 'completed')
                ->with('course')
                ->get();

            $emailService->sendEmail(
                $this->user,
                'morning-plan',
                \App\Mail\MorningPlanEmail::class,
                [
                    'subject' => 'Your Daily Study Plan',
                    'plans' => $plans,
                ]
            );
        } catch (\Exception $e) {
            Log::error("Failed to send morning email to user {$this->user->id}: " . $e->getMessage());
            throw $e;
        }
    }
}

