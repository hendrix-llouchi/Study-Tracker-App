<?php

namespace App\Console\Commands;

use App\Jobs\SendMorningEmailJob;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendMorningEmailsCommand extends Command
{
    protected $signature = 'app:send-morning-emails';
    protected $description = 'Send morning study plan emails to users';

    public function handle(): void
    {
        $now = now();
        $currentTime = $now->format('H:i');

        // Get users with email notifications enabled
        $users = User::whereHas('preferences', function ($query) {
            $query->where('email_notifications', true);
        })->with('preferences')->get();

        $queuedCount = 0;

        foreach ($users as $user) {
            $preferences = $user->preferences;
            if (!$preferences) {
                continue;
            }

            $emailTime = Carbon::parse($preferences->morning_email_time)->format('H:i');
            
            // Check if current time has passed the user's preferred email time
            if ($currentTime < $emailTime) {
                continue;
            }

            // Check if email was already sent today (prevent duplicates)
            $alreadySent = \App\Models\EmailLog::where('user_id', $user->id)
                ->where('email_type', 'morning-plan')
                ->where('status', 'sent')
                ->whereDate('sent_at', $now->toDateString())
                ->exists();

            if (!$alreadySent) {
                SendMorningEmailJob::dispatch($user);
                $queuedCount++;
            }
        }

        $this->info("Morning emails queued successfully. {$queuedCount} emails queued.");
    }
}

