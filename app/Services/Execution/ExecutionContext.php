<?php

namespace App\Services\Execution;

use App\Models\Execution;

class ExecutionContext
{
    public function build(Execution $execution, $nodes): array
    {
        $context = [
            'execution' => [
                'id' => $execution->id,
                'mode' => $execution->mode,
            ],
            'workflow' => [
                'id' => $execution->workflow_id,
                'name' => $execution->workflow->name,
                'active' => $execution->workflow->active,
            ],
            'nodes' => [],
        ];
        
        // Add executed nodes data
        foreach ($execution->executionData as $nodeData) {
            $context['nodes'][$nodeData->node_id] = [
                'json' => $nodeData->output_data,
                'binary' => [],
            ];
        }
        
        return $context;
    }
}