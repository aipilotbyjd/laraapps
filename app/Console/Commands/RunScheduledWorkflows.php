<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use App\Jobs\ExecuteWorkflowJob;
use Illuminate\Console\Command;

class RunScheduledWorkflows extends Command
{
    protected $signature = 'workflows:run-scheduled';
    protected $description = 'Execute scheduled workflows that are due';
    
    public function handle(): int
    {
        $schedules = Schedule::where('active', true)
            ->where('next_run_at', '<=', now())
            ->with('workflow')
            ->get();
        
        foreach ($schedules as $schedule) {
            if (!$schedule->workflow->active) {
                continue;
            }
            
            $this->info("Executing workflow: {$schedule->workflow->name}");
            
            ExecuteWorkflowJob::dispatch(
                $schedule->workflow,
                [],
                'schedule'
            );
            
            $schedule->markAsRun();
        }
        
        $this->info("Executed {$schedules->count()} scheduled workflows");
        
        return 0;
    }
}