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
        $currentHour = now()->format('H');
        $currentMinute = now()->format('i');

        $users = User::whereHas('preferences', function ($query) {
            $query->where(function ($q) {
                $q->where('email_notifications', true)
                  ->orWhere('push_notifications', true);
            });
        })->with('preferences')->get();

        foreach ($users as $user) {
            $preferences = $user->preferences;
            if (!$preferences) {
                continue;
            }

            $reminderTime = Carbon::parse($preferences->reminder_time);
            $userHour = $reminderTime->format('H');
            $userMinute = $reminderTime->format('i');

            if ($userHour == $currentHour && $userMinute <= $currentMinute) {
                SendReminderJob::dispatch($user);
            }
        }

        $this->info('Reminders queued successfully.');
    }
}

