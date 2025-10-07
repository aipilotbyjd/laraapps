<?php

namespace App\Jobs;

use App\Models\WaitingExecution;
use App\Services\Execution\WorkflowExecutor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResumeExecutionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $connection;
    public $queue;
    
    public function __construct(
        public WaitingExecution $waitingExecution,
        public array $resumeData = []
    ) {
        $this->connection = config('workflow.queue.connection', 'database');
        $this->queue = config('workflow.queue.name', 'workflows');
    }
    
    public function handle(WorkflowExecutor $executor): void
    {
        $execution = $this->waitingExecution->execution;
        
        $executor->resume($execution, $this->resumeData);
    }
}