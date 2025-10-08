<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workflow;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkflowPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('workflow.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Workflow $workflow): bool
    {
        return $user->hasPermissionTo('workflow.view') && $workflow->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('workflow.create') && $user->canCreateMoreWorkflows();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Workflow $workflow): bool
    {
        return $user->hasPermissionTo('workflow.update') && $workflow->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Workflow $workflow): bool
    {
        return $user->hasPermissionTo('workflow.delete') && $workflow->user_id === $user->id;
    }

    /**
     * Determine whether the user can execute workflows (for create permission check).
     */
    public function execute(User $user, $workflow = null): bool
    {
        // If checking general execute permission (without specific workflow)
        if ($workflow === null) {
            return $user->hasPermissionTo('workflow.execute');
        }
        
        // If checking execute permission for specific workflow
        return $user->hasPermissionTo('workflow.execute') && $workflow->user_id === $user->id;
    }

    /**
     * Determine whether the user can duplicate the workflow.
     */
    public function duplicate(User $user, Workflow $workflow): bool
    {
        return $user->hasPermissionTo('workflow.duplicate') && 
               $workflow->user_id === $user->id && 
               $user->canCreateMoreWorkflows();
    }

    /**
     * Determine whether the user can activate the workflow.
     */
    public function activate(User $user, Workflow $workflow): bool
    {
        return $user->hasPermissionTo('workflow.activate') && $workflow->user_id === $user->id;
    }

    /**
     * Determine whether the user can deactivate the workflow.
     */
    public function deactivate(User $user, Workflow $workflow): bool
    {
        return $user->hasPermissionTo('workflow.deactivate') && $workflow->user_id === $user->id;
    }
}
