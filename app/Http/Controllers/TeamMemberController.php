<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        DB::transaction(function () use ($team, $user, $request) {
            $team->members()->syncWithoutDetaching([$user->id => ['role' => $request->role]]);
            $user->assignRole($request->role, $team->id);
        });

        return response()->json(['message' => 'User added to team successfully.']);
    }

    public function destroy(Team $team, User $user)
    {
        $this->authorize('update', $team);

        DB::transaction(function () use ($team, $user) {
            $pivot = DB::table('team_user')->where('team_id', $team->id)->where('user_id', $user->id)->first();

            if ($pivot) {
                // This is a placeholder for proper role removal logic.
                // Spatie doesn't have a simple `removeRoleFromTeam` method.
            }

            $team->members()->detach($user->id);
        });

        return response()->json(['message' => 'User removed from team successfully.']);
    }
}