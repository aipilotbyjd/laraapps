<?php

namespace App\Jobs;

use App\Models\Workflow;
use App\Models\Execution;
use App\Services\Execution\WorkflowExecutor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExecuteWorkflowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $connection;
    public $queue;
    public $timeout = 300;
    public $tries = 3;
    public $backoff = [60, 180, 600];
    
    public function __construct(
        public Workflow $workflow,
        public array $inputData = [],
        public string $mode = 'trigger',
        public ?Execution $execution = null
    ) {
        $this->connection = config('workflow.queue.connection', 'database');
        $this->queue = config('workflow.queue.name', 'workflows');
    }
    
    public function handle(WorkflowExecutor $executor): void
    {
        if ($this->execution) {
            // Continue existing execution
            $executor->resume($this->execution, $this->inputData);
        } else {
            // Start new execution
            $executor->execute($this->workflow, $this->inputData, $this->mode);
        }
    }
    
    public function failed(\Throwable $exception): void
    {
        if ($this->execution) {
            $this->execution->markAsFailed($exception->getMessage());
        }
        
        \Log::error('Workflow execution job failed', [
            'workflow_id' => $this->workflow->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}