<?php

namespace App\Console\Commands;

use App\Jobs\CleanupOldDataJob;
use Illuminate\Console\Command;

class CleanupOldDataCommand extends Command
{
    protected $signature = 'app:cleanup-old-data';
    protected $description = 'Clean up old notifications and logs';

    public function handle(): void
    {
        CleanupOldDataJob::dispatch();
        $this->info('Cleanup job queued successfully.');
    }
}

