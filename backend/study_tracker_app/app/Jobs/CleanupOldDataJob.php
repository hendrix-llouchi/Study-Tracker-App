<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\EmailLog;
use App\Models\ActivityLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanupOldDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Delete notifications older than 90 days
        Notification::where('created_at', '<', now()->subDays(90))->delete();

        // Delete email logs older than 180 days
        EmailLog::where('created_at', '<', now()->subDays(180))->delete();

        // Delete activity logs older than 365 days
        ActivityLog::where('created_at', '<', now()->subDays(365))->delete();
    }
}

