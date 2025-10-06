<?php

namespace App\Http\Controllers;

use App\Models\Workflow;
use App\Models\Execution;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        
        $stats = [
            'workflows' => [
                'total' => Workflow::where('user_id', $userId)->count(),
                'active' => Workflow::where('user_id', $userId)->where('active', true)->count(),
            ],
            'executions' => [
                'total' => Execution::whereHas('workflow', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })->count(),
                'successful' => Execution::whereHas('workflow', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })->where('status', 'success')->count(),
                'failed' => Execution::whereHas('workflow', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })->where('status', 'failed')->count(),
                'running' => Execution::whereHas('workflow', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })->where('status', 'running')->count(),
            ],
            'recent_executions' => Execution::with('workflow')
                ->whereHas('workflow', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($execution) {
                    return [
                        'id' => $execution->id,
                        'workflow_name' => $execution->workflow->name,
                        'status' => $execution->status,
                        'mode' => $execution->mode,
                        'started_at' => $execution->started_at,
                        'finished_at' => $execution->finished_at,
                        'duration_ms' => $execution->duration_ms,
                    ];
                }),
        ];
        
        return response()->json($stats);
    }
}