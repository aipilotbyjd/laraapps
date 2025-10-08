<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class TeamMemberController extends Controller
{
    public function store(Request $request, Team $team)
    {
        $this->authorize('update', $team);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::findOrFail($request->user_id);

        $team->members()->attach($user);

        $user->assignRole($request->role, $team->id);

        return response()->json(['message' => 'User added to team successfully.']);
    }

    public function destroy(Team $team, User $user)
    {
        $this->authorize('update', $team);

        $team->members()->detach($user);

        // It's not currently possible to remove a role scoped to a team.
        // See: https://github.com/spatie/laravel-permission/issues/1125
        // $user->removeRole($request->role, $team->id);

        return response()->json(['message' => 'User removed from team successfully.']);
    }
}