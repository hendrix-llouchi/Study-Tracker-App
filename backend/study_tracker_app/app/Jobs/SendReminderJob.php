<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Notification;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900];

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
            
            if (!$preferences) {
                return;
            }

            // Check if user has created plan for tomorrow
            $tomorrow = now()->addDay();
            $hasPlan = $this->user->studyPlans()
                ->where('date', $tomorrow->format('Y-m-d'))
                ->exists();

            if (!$hasPlan && ($preferences->email_notifications || $preferences->push_notifications)) {
                // Send reminder
                if ($preferences->email_notifications) {
                    $emailService->sendEmail(
                        $this->user,
                        'reminder',
                        \App\Mail\StudyReminderEmail::class,
                        [
                            'subject' => 'Don\'t forget to set your study plan!',
                        ]
                    );
                }

                // Create notification
                Notification::create([
                    'user_id' => $this->user->id,
                    'type' => 'reminder',
                    'title' => 'Study Plan Reminder',
                    'message' => 'Don\'t forget to set your study plan for tomorrow!',
                    'action_url' => '/planning',
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to send reminder to user {$this->user->id}: " . $e->getMessage());
            throw $e;
        }
    }
}

