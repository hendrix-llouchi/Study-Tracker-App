<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\EmailService;
use App\Services\WeeklyReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateWeeklyReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public string $weekStart
    ) {}

    /**
     * Execute the job.
     */
    public function handle(WeeklyReportService $reportService, EmailService $emailService): void
    {
        try {
            $preferences = $this->user->preferences;
            
            if (!$preferences || !$preferences->weekly_report_enabled) {
                return;
            }

            // Generate report
            $report = $reportService->generateWeeklyReport($this->user, $this->weekStart);

            // Send email
            if ($preferences->email_notifications) {
                $emailService->sendEmail(
                    $this->user,
                    'weekly-report',
                    \App\Mail\WeeklyReportEmail::class,
                    [
                        'subject' => 'Your Weekly Study Report',
                        'report' => $report,
                    ]
                );

                $report->update(['email_sent_at' => now()]);
            }

            // Create notification
            \App\Models\Notification::create([
                'user_id' => $this->user->id,
                'type' => 'report',
                'title' => 'Weekly Report Ready',
                'message' => 'Your weekly study report is ready!',
                'action_url' => '/progress',
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to generate weekly report for user {$this->user->id}: " . $e->getMessage());
            throw $e;
        }
    }
}

