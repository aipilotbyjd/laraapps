<?php

namespace App\Observers;

use App\Models\Execution;

class ExecutionObserver
{
    public function created(Execution $execution): void
    {
        //
    }

    public function updated(Execution $execution): void
    {
        // Dispatch events based on status changes
        if ($execution->isDirty('status')) {
            if ($execution->status === 'success') {
                event(new \App\Events\WorkflowExecutionCompleted($execution));
            } elseif ($execution->status === 'failed') {
                event(new \App\Events\WorkflowExecutionFailed($execution));
            }
        }
    }

    public function deleted(Execution $execution): void
    {
        // Clean up execution data when execution is deleted
        $execution->executionData()->delete();
    }

    public function restored(Execution $execution): void
    {
        //
    }

    public function forceDeleted(Execution $execution): void
    {
        //
    }
}