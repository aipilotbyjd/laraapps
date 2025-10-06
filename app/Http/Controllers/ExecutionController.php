<?php

namespace App\Http\Controllers;

use App\Models\Execution;
use App\Http\Resources\ExecutionResource;
use Illuminate\Http\Request;

class ExecutionController extends Controller
{
    public function index(Request $request)
    {
        $executions = Execution::with(['workflow'])
            ->whereHas('workflow', function($query) {
                $query->where('user_id', auth()->id());
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return ExecutionResource::collection($executions);
    }

    public function show(Execution $execution)
    {
        $this->authorize('view', $execution->workflow);
        
        return new ExecutionResource($execution->load(['executionData', 'workflow']));
    }

    public function retry(Execution $execution)
    {
        $this->authorize('execute', $execution->workflow);
        
        if (!$execution->canRetry()) {
            return response()->json(['error' => 'Execution cannot be retried'], 400);
        }

        $newExecution = $execution->retry();
        
        return new ExecutionResource($newExecution);
    }

    public function destroy(Execution $execution)
    {
        $this->authorize('delete', $execution->workflow);
        
        $execution->delete();

        return response()->noContent();
    }
}