<?php

namespace App\Services\Execution;

use App\Models\Execution;
use App\Models\ExecutionData;
use Illuminate\Support\Facades\Log;

class ExecutionLogger
{
    public function logNodeStart(Execution $execution, array $node, array $input): ExecutionData
    {
        return ExecutionData::create([
            'execution_id' => $execution->id,
            'node_id' => $node['id'],
            'node_name' => $node['name'] ?? $node['type'],
            'node_type' => $node['type'],
            'status' => 'running',
            'input_data' => $input,
            'started_at' => now(),
        ]);
    }
    
    public function logNodeSuccess(ExecutionData $nodeExecution, array $output): void
    {
        $nodeExecution->update([
            'status' => 'success',
            'output_data' => $output,
            'finished_at' => now(),
            'duration_ms' => $nodeExecution->started_at 
                ? now()->diffInMilliseconds($nodeExecution->started_at) 
                : null,
        ]);
    }
    
    public function logNodeError(ExecutionData $nodeExecution, string $error): void
    {
        $nodeExecution->update([
            'status' => 'failed',
            'error' => $error,
            'finished_at' => now(),
            'duration_ms' => $nodeExecution->started_at 
                ? now()->diffInMilliseconds($nodeExecution->started_at) 
                : null,
        ]);
    }
    
    public function logError(Execution $execution, \Exception $e): void
    {
        Log::error('Workflow execution failed', [
            'execution_id' => $execution->id,
            'workflow_id' => $execution->workflow_id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
}