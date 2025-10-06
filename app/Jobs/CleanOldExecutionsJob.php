<?php

namespace App\Jobs;

use App\Models\Execution;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanOldExecutionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function handle(): void
    {
        $daysToKeep = config('workflow.execution_retention_days', 30);
        
        Execution::where('created_at', '<', now()->subDays($daysToKeep))
            ->where('status', '!=', 'failed') // Keep failed executions longer
            ->delete();
        
        // Delete very old failed executions
        Execution::where('created_at', '<', now()->subDays($daysToKeep * 3))
            ->where('status', 'failed')
            ->delete();
    }
}