<?php

namespace App\Listeners;

use App\Events\WorkflowExecutionStarted;
use Illuminate\Support\Facades\Log;

class LogExecutionStarted
{
    public function handle(WorkflowExecutionStarted $event): void
    {
        Log::info('Workflow execution started', [
            'execution_id' => $event->execution->id,
            'workflow_id' => $event->execution->workflow_id,
            'mode' => $event->execution->mode,
        ]);
    }
}