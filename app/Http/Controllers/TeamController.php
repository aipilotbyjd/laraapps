<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Team;
use App\Http\Resources\TeamResource;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(Organization $organization)
    {
        $this->authorize('viewAny', [Team::class, $organization]);

        return TeamResource::collection($organization->teams);
    }

    public function store(Request $request, Organization $organization)
    {
        $this->authorize('create', [Team::class, $organization]);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $team = $organization->teams()->create([
            'name' => $request->name,
            'user_id' => auth()->id(),
        ]);

        return new TeamResource($team);
    }

    public function show(Team $team)
    {
        $this->authorize('view', $team);

        return new TeamResource($team->load('organization', 'owner'));
    }

    public function update(Request $request, Team $team)
    {
        $this->authorize('update', $team);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $team->update($request->only('name'));

        return new TeamResource($team);
    }

    public function destroy(Team $team)
    {
        $this->authorize('delete', $team);

        $team->delete();

        return response()->noContent();
    }
}