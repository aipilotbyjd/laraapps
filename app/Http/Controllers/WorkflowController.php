<?php

namespace App\Http\Controllers;

use App\Models\Workflow;
use App\Http\Requests\StoreWorkflowRequest;
use App\Http\Requests\UpdateWorkflowRequest;
use App\Http\Resources\WorkflowResource;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    public function index(Request $request)
    {
        $workflows = Workflow::with(['user', 'tags'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return WorkflowResource::collection($workflows);
    }

    public function show(Workflow $workflow)
    {
        $this->authorize('view', $workflow);
        
        return new WorkflowResource($workflow->load(['executions', 'versions', 'webhooks', 'schedules', 'tags']));
    }

    public function store(StoreWorkflowRequest $request)
    {
        $workflow = Workflow::create([
            'name' => $request->name,
            'description' => $request->description,
            'active' => $request->active ?? false,
            'nodes' => $request->nodes ?? [],
            'connections' => $request->connections ?? [],
            'settings' => $request->settings ?? [],
            'static_data' => $request->static_data ?? [],
            'user_id' => auth()->id(),
        ]);

        return new WorkflowResource($workflow);
    }

    public function update(UpdateWorkflowRequest $request, Workflow $workflow)
    {
        $this->authorize('update', $workflow);
        
        $workflow->update([
            'name' => $request->name,
            'description' => $request->description,
            'active' => $request->active,
            'nodes' => $request->nodes,
            'connections' => $request->connections,
            'settings' => $request->settings,
            'static_data' => $request->static_data,
        ]);

        return new WorkflowResource($workflow);
    }

    public function destroy(Workflow $workflow)
    {
        $this->authorize('delete', $workflow);
        
        $workflow->delete();

        return response()->noContent();
    }

    public function execute(Request $request, Workflow $workflow)
    {
        $this->authorize('execute', $workflow);
        
        // Execute the workflow with provided input
        $inputData = $request->input('input_data', []);
        $execution = app('workflow.executor')->execute($workflow, $inputData, 'manual');
        
        return response()->json([
            'execution_id' => $execution->id,
            'status' => $execution->status,
        ]);
    }
}