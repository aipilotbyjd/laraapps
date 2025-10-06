<?php

namespace App\Console\Commands;

use App\Models\WaitingExecution;
use App\Jobs\ResumeExecutionJob;
use Illuminate\Console\Command;

class ProcessWaitingExecutions extends Command
{
    protected $signature = 'workflows:process-waiting';
    protected $description = 'Resume waiting executions that are ready';
    
    public function handle(): int
    {
        $waitingExecutions = WaitingExecution::where('wait_type', 'time')
            ->where('resume_at', '<=', now())
            ->get();
        
        foreach ($waitingExecutions as $waiting) {
            $this->info("Resuming execution: {$waiting->execution_id}");
            
            ResumeExecutionJob::dispatch($waiting);
        }
        
        $this->info("Resumed {$waitingExecutions->count()} executions");
        
        return 0;
    }
}