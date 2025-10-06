<?php

namespace App\Http\Controllers;

use App\Models\Credential;
use Illuminate\Http\Request;

class CredentialController extends Controller
{
    public function index(Request $request)
    {
        $credentials = Credential::where('user_id', $request->user()->id)
            ->select(['id', 'name', 'type', 'last_used_at', 'created_at'])
            ->latest()
            ->get();
        
        return response()->json($credentials);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:api_key,oauth2,basic_auth,bearer_token',
            'data' => 'required|array',
        ]);
        
        $credential = Credential::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'data' => $validated['data'],
            'user_id' => $request->user()->id,
        ]);
        
        return response()->json([
            'id' => $credential->id,
            'name' => $credential->name,
            'type' => $credential->type,
            'last_used_at' => $credential->last_used_at,
            'created_at' => $credential->created_at,
        ], 201);
    }
    
    public function show(Credential $credential)
    {
        $this->authorize('view', $credential);
        
        return response()->json([
            'id' => $credential->id,
            'name' => $credential->name,
            'type' => $credential->type,
            'data' => $credential->data, // Decrypted
            'last_used_at' => $credential->last_used_at,
            'created_at' => $credential->created_at,
        ]);
    }
    
    public function update(Request $request, Credential $credential)
    {
        $this->authorize('update', $credential);
        
        $validated = $request->validate([
            'name' => 'string|max:255',
            'data' => 'array',
        ]);
        
        if (isset($validated['name'])) {
            $credential->update(['name' => $validated['name']]);
        }
        
        if (isset($validated['data'])) {
            $credential->update(['data' => $validated['data']]);
        }
        
        return response()->json(['message' => 'Credential updated']);
    }
    
    public function destroy(Credential $credential)
    {
        $this->authorize('delete', $credential);
        
        $credential->delete();
        
        return response()->noContent();
    }
    
    public function test(Credential $credential)
    {
        $this->authorize('view', $credential);
        
        try {
            $result = app(\App\Services\Credential\CredentialService::class)->test($credential);
            
            return response()->json([
                'success' => $result,
                'message' => $result ? 'Credential is valid' : 'Credential test failed'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}