<?php

namespace App\Listeners;

use App\Events\WorkflowExecutionFailed;
use Illuminate\Support\Facades\Notification;

class NotifyExecutionFailure
{
    public function handle(WorkflowExecutionFailed $event): void
    {
        $execution = $event->execution;
        $workflow = $execution->workflow;
        
        // Send notification to workflow owner
        if ($workflow->user) {
            // $workflow->user->notify(new WorkflowFailedNotification($execution));
        }
        
        // Log to monitoring service
        \Log::channel('workflow')->error('Workflow execution failed', [
            'execution_id' => $execution->id,
            'workflow_id' => $workflow->id,
            'error' => $execution->error_message,
        ]);
    }
}