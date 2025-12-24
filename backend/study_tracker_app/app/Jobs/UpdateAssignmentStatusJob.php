<?php

namespace App\Jobs;

use App\Models\Assignment;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateAssignmentStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $overdueAssignments = Assignment::where('status', 'pending')
            ->where('due_date', '<', now())
            ->get();

        foreach ($overdueAssignments as $assignment) {
            $assignment->update(['status' => 'overdue']);

            // Create notification
            Notification::create([
                'user_id' => $assignment->user_id,
                'type' => 'assignment',
                'title' => 'Assignment Overdue',
                'message' => "Your assignment '{$assignment->title}' is now overdue.",
                'action_url' => "/assignments/{$assignment->id}",
            ]);
        }
    }
}

