<?php

namespace App\Http\Controllers;

use App\Models\Credential;
use Illuminate\Http\Request;

class CredentialController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Credential::class, 'credential');
    }

    public function index(Request $request)
    {
        $credentials = $request->user()->credentials()->latest()->get();
        return response()->json($credentials);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'data' => 'required|array',
        ]);

        $credential = $request->user()->credentials()->create($validated);

        return response()->json($credential, 201);
    }

    public function show(Credential $credential)
    {
        return response()->json($credential);
    }

    public function update(Request $request, Credential $credential)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'data' => 'sometimes|required|array',
        ]);

        $credential->update($validated);

        return response()->json($credential);
    }

    public function destroy(Credential $credential)
    {
        $credential->delete();
        return response()->noContent();
    }
}
