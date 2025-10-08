<?php

namespace App\Policies;

use App\Models\Invitation;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvitationPolicy
{
    use HandlesAuthorization;

    public function create(User $user, Team $team): bool
    {
        return $user->hasPermissionTo('team.manage', $team->id);
    }
}