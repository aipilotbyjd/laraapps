<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Http\Resources\OrganizationResource;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Organization::class);

        return OrganizationResource::collection(auth()->user()->organizations()->with(['owner', 'users', 'teams'])->get());
    }

    public function store(Request $request)
    {
        $this->authorize('create', Organization::class);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $organization = auth()->user()->ownedOrganizations()->create([
            'name' => $request->name,
        ]);

        auth()->user()->organizations()->attach($organization);

        // Create a default team and assign the user as the owner.
        $team = $organization->teams()->create([
            'name' => 'General',
            'user_id' => auth()->id(),
        ]);

        $team->members()->attach(auth()->user());

        auth()->user()->assignRole('owner', $team->id);

        return new OrganizationResource($organization);
    }

    public function show(Organization $organization)
    {
        $this->authorize('view', $organization);

        return new OrganizationResource($organization->load('teams', 'users'));
    }

    public function update(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $organization->update($request->only('name'));

        return new OrganizationResource($organization);
    }

    public function destroy(Organization $organization)
    {
        $this->authorize('delete', $organization);

        $organization->delete();

        return response()->noContent();
    }
}