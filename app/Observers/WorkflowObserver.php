<?php

namespace App\Observers;

use App\Models\Workflow;

class WorkflowObserver
{
    public function created(Workflow $workflow): void
    {
        // Add version when workflow is created
        $workflow->createVersion();
    }

    public function updated(Workflow $workflow): void
    {
        // Add version on update when workflow structure changes
        if ($workflow->isDirty(['nodes', 'connections'])) {
            $workflow->createVersion();
        }
    }

    public function deleted(Workflow $workflow): void
    {
        // Clean up related data when workflow is deleted
    }

    public function restored(Workflow $workflow): void
    {
        //
    }

    public function forceDeleted(Workflow $workflow): void
    {
        //
    }
}