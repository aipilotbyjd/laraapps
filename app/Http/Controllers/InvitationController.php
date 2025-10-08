<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function store(Request $request, Team $team)
    {
        $this->authorize('create', [Invitation::class, $team]);

        $request->validate([
            'email' => 'required|email',
            'role' => 'required|string|exists:roles,name',
        ]);

        $invitation = $team->invitations()->create([
            'email' => $request->email,
            'organization_id' => $team->organization_id,
            'role' => $request->role,
            'token' => Str::random(32),
        ]);

        // In a real application, you would email the invitation to the user.
        // Mail::to($request->email)->send(new TeamInvitationMail($invitation));

        return response()->json(['message' => 'Invitation sent successfully.', 'invitation' => $invitation]);
    }

    public function accept(Request $request, Invitation $invitation)
    {
        $user = User::where('email', $invitation->email)->first();

        if (! $user) {
            // In a real application, you would redirect the user to the registration page.
            return response()->json(['message' => 'User not found. Please register first.'], 404);
        }

        $invitation->team->members()->attach($user);
        $user->assignRole($invitation->role, $invitation->team_id);

        $invitation->delete();

        return response()->json(['message' => 'Invitation accepted successfully.']);
    }
}