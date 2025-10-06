<?php

namespace App\Console\Commands;

use App\Jobs\CleanOldExecutionsJob;
use Illuminate\Console\Command;

class CleanupExecutions extends Command
{
    protected $signature = 'workflows:cleanup-executions';
    protected $description = 'Clean up old execution records';
    
    public function handle(): int
    {
        CleanOldExecutionsJob::dispatch();
        
        $this->info('Cleanup job dispatched');
        
        return 0;
    }
}