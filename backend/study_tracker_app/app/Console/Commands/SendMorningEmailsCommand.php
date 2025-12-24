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
        $currentHour = now()->format('H');
        $currentMinute = now()->format('i');

        // Get users with email notifications enabled
        $users = User::whereHas('preferences', function ($query) {
            $query->where('email_notifications', true);
        })->with('preferences')->get();

        foreach ($users as $user) {
            $preferences = $user->preferences;
            if (!$preferences) {
                continue;
            }

            $emailTime = Carbon::parse($preferences->morning_email_time);
            $userHour = $emailTime->format('H');
            $userMinute = $emailTime->format('i');

            // Check if it's time to send email (within current hour)
            if ($userHour == $currentHour && $userMinute <= $currentMinute) {
                SendMorningEmailJob::dispatch($user);
            }
        }

        $this->info('Morning emails queued successfully.');
    }
}

