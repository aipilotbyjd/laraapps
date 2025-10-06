<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $tags = Tag::where('user_id', $request->user()->id)
            ->latest()
            ->get();
        
        return response()->json($tags);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name,NULL,id,user_id,' . $request->user()->id,
            'color' => 'string|regex:/^#[0-9a-fA-F]{6}$/',
        ]);
        
        $tag = Tag::create([
            'name' => $validated['name'],
            'color' => $validated['color'] ?? '#1f77b4',
            'user_id' => $request->user()->id,
        ]);
        
        return response()->json($tag, 201);
    }
    
    public function show(Tag $tag)
    {
        $this->authorize('view', $tag);
        
        return response()->json($tag);
    }
    
    public function update(Request $request, Tag $tag)
    {
        $this->authorize('update', $tag);
        
        $validated = $request->validate([
            'name' => 'string|max:255|unique:tags,name,' . $tag->id . ',id,user_id,' . $request->user()->id,
            'color' => 'string|regex:/^#[0-9a-fA-F]{6}$/',
        ]);
        
        $tag->update($validated);
        
        return response()->json($tag);
    }
    
    public function destroy(Tag $tag)
    {
        $this->authorize('delete', $tag);
        
        $tag->delete();
        
        return response()->noContent();
    }
}