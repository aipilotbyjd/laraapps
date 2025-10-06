<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $schedules = Schedule::with('workflow')
            ->whereHas('workflow', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->latest()
            ->get();
        
        return response()->json($schedules);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'workflow_id' => 'required|exists:workflows,id',
            'node_id' => 'required|string',
            'cron_expression' => 'required|string',
            'timezone' => 'string|timezone',
            'active' => 'boolean',
        ]);
        
        $workflow = \App\Models\Workflow::findOrFail($validated['workflow_id']);
        $this->authorize('update', $workflow);
        
        $schedule = Schedule::create([
            ...$validated,
            'active' => $validated['active'] ?? true,
            'timezone' => $validated['timezone'] ?? 'UTC',
        ]);
        
        return response()->json($schedule, 201);
    }
    
    public function show(Schedule $schedule)
    {
        $this->authorize('view', $schedule->workflow);
        
        return response()->json($schedule);
    }
    
    public function update(Request $request, Schedule $schedule)
    {
        $this->authorize('update', $schedule->workflow);
        
        $validated = $request->validate([
            'cron_expression' => 'string',
            'timezone' => 'string|timezone',
            'active' => 'boolean',
        ]);
        
        $schedule->update($validated);
        
        return response()->json($schedule);
    }
    
    public function destroy(Schedule $schedule)
    {
        $this->authorize('delete', $schedule->workflow);
        
        $schedule->delete();
        
        return response()->noContent();
    }
}