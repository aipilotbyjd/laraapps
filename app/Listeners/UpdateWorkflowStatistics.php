<?php

namespace App\Listeners;

use App\Events\WorkflowExecutionCompleted;
use App\Events\WorkflowExecutionFailed;

class UpdateWorkflowStatistics
{
    public function handleCompleted(WorkflowExecutionCompleted $event): void
    {
        $workflow = $event->execution->workflow;
        $workflow->increment('execution_count');
        $workflow->update(['last_executed_at' => now()]);
    }
    
    public function handleFailed(WorkflowExecutionFailed $event): void
    {
        $workflow = $event->execution->workflow;
        $workflow->increment('execution_count');
        $workflow->update(['last_executed_at' => now()]);
    }
}