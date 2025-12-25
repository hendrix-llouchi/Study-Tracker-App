<?php

namespace App\Console\Commands;

use App\Jobs\SendReminderJob;
use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendRemindersCommand extends Command
{
    protected $signature = 'app:send-reminders';
    protected $description = 'Send reminders to users who haven\'t set study plans';

    public function handle(): void
    {
        $now = now();
        $currentTime = $now->format('H:i');

        $users = User::whereHas('preferences', function ($query) {
            $query->where(function ($q) {
                $q->where('email_notifications', true)
                  ->orWhere('push_notifications', true);
            });
        })->with('preferences')->get();

        $queuedCount = 0;

        foreach ($users as $user) {
            $preferences = $user->preferences;
            if (!$preferences) {
                continue;
            }

            $reminderTime = Carbon::parse($preferences->reminder_time)->format('H:i');
            
            // Check if current time has passed the user's preferred reminder time
            if ($currentTime < $reminderTime) {
                continue;
            }

            // Check if reminder was already sent today (prevent duplicates)
            $alreadySent = \App\Models\EmailLog::where('user_id', $user->id)
                ->where('email_type', 'reminder')
                ->where('status', 'sent')
                ->whereDate('sent_at', $now->toDateString())
                ->exists();

            if (!$alreadySent) {
                SendReminderJob::dispatch($user);
                $queuedCount++;
            }
        }

        $this->info("Reminders queued successfully. {$queuedCount} reminders queued.");
    }
}

