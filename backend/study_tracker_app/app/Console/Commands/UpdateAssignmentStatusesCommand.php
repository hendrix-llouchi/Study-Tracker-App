<?php

namespace App\Console\Commands;

use App\Jobs\UpdateAssignmentStatusJob;
use Illuminate\Console\Command;

class UpdateAssignmentStatusesCommand extends Command
{
    protected $signature = 'app:update-assignment-statuses';
    protected $description = 'Update assignment statuses to overdue';

    public function handle(): void
    {
        UpdateAssignmentStatusJob::dispatch();
        $this->info('Assignment status update queued successfully.');
    }
}

