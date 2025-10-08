<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;

class CurrentTeamController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'team_id' => 'required|exists:teams,id',
        ]);

        $team = Team::findOrFail($request->team_id);

        if (! auth()->user()->belongsToTeam($team)) {
            return response()->json(['message' => 'You do not belong to this team.'], 403);
        }

        auth()->user()->update([
            'current_team_id' => $team->id,
        ]);

        return response()->json(['message' => 'Current team updated successfully.']);
    }
}