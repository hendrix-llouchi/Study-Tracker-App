<?php

namespace App\Console\Commands;

use App\Jobs\GenerateWeeklyReportJob;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateWeeklyReportsCommand extends Command
{
    protected $signature = 'app:generate-weekly-reports';
    protected $description = 'Generate weekly reports for users';

    public function handle(): void
    {
        $today = now();
        $dayName = $today->format('l');

        $users = User::whereHas('preferences', function ($query) use ($dayName) {
            $query->where('weekly_report_enabled', true)
                  ->where('weekly_report_day', $dayName);
        })->with('preferences')->get();

        foreach ($users as $user) {
            // Generate report for last week (Monday to Sunday)
            $weekStart = $today->copy()->subWeek()->startOfWeek();
            
            GenerateWeeklyReportJob::dispatch($user, $weekStart->format('Y-m-d'));
        }

        $this->info('Weekly reports queued successfully.');
    }
}

