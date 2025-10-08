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
        $this->authorize('create', Workflow::class);
        
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

    public function duplicate(Workflow $workflow)
    {
        $this->authorize('duplicate', $workflow);
        
        $duplicated = $workflow->replicate();
        $duplicated->name = $workflow->name . ' (Copy)';
        $duplicated->user_id = auth()->id();
        $duplicated->save();
        
        // Also duplicate the versions
        foreach ($workflow->versions as $version) {
            $duplicatedVersion = $version->replicate();
            $duplicatedVersion->workflow_id = $duplicated->id;
            $duplicatedVersion->save();
        }
        
        return new WorkflowResource($duplicated);
    }

    public function activate(Workflow $workflow)
    {
        $this->authorize('activate', $workflow);
        
        $workflow->update(['active' => true]);
        
        return response()->json([
            'message' => 'Workflow activated successfully'
        ]);
    }

    public function deactivate(Workflow $workflow)
    {
        $this->authorize('deactivate', $workflow);
        
        $workflow->update(['active' => false]);
        
        return response()->json([
            'message' => 'Workflow deactivated successfully'
        ]);
    }

    public function versions(Workflow $workflow)
    {
        $this->authorize('view', $workflow);
        
        return response()->json($workflow->versions()->latest()->get());
    }

    public function restoreVersion(Request $request, Workflow $workflow, $versionId)
    {
        $this->authorize('update', $workflow);
        
        $version = $workflow->versions()->findOrFail($versionId);
        
        $workflow->update([
            'nodes' => $version->nodes,
            'connections' => $version->connections,
            'settings' => $version->settings,
        ]);
        
        return new WorkflowResource($workflow);
    }
}