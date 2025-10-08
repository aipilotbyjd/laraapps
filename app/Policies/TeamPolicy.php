<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user, Organization $organization): bool
    {
        return $user->belongsToOrganization($organization);
    }

    public function view(User $user, Team $team): bool
    {
        return $user->belongsToOrganization($team->organization);
    }

    public function create(User $user, Organization $organization): bool
    {
        return $user->belongsToOrganization($organization);
    }

    public function update(User $user, Team $team): bool
    {
        return $user->hasPermissionTo('team.manage', $team->id);
    }

    public function delete(User $user, Team $team): bool
    {
        return $user->hasPermissionTo('team.manage', $team->id);
    }
}
