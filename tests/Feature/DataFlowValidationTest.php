<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Workflow;
use App\Models\Execution;
use App\Services\Execution\WorkflowExecutor;
use App\Services\Execution\ExecutionContext;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DataFlowValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_context_building_and_data_flow(): void
    {
        $user = \App\Models\User::factory()->create();
        
        // Create a workflow with multiple nodes to test data flow
        $workflow = Workflow::create([
            'name' => 'Data Flow Test',
            'nodes' => [
                [
                    'id' => 'trigger_1',
                    'type' => 'manual_trigger',
                    'name' => 'Manual Trigger',
                    'parameters' => []
                ],
                [
                    'id' => 'set_1',
                    'type' => 'set', 
                    'name' => 'Set Node',
                    'parameters' => [
                        'values' => [
                            [
                                'name' => 'new_field',
                                'setTo' => 'processed_{{$json.original_value}}'
                            ]
                        ]
                    ]
                ]
            ],
            'connections' => [
                [
                    'source' => 'trigger_1',
                    'target' => 'set_1'
                ]
            ],
            'active' => true,
            'user_id' => $user->id
        ]);

        // Execute workflow with initial data
        $executor = app(WorkflowExecutor::class);
        $execution = $executor->execute($workflow, ['original_value' => 'test'], 'manual');

        // Validate execution was successful
        $this->assertEquals('success', $execution->fresh()->status);
        
        // Check that execution data was properly recorded
        $executionData = $execution->executionData;
        $this->assertCount(2, $executionData); // trigger + set node
        
        // Find the set node execution data
        $setNodeExecution = $executionData->first(function($item) {
            return $item->node_type === 'set';
        });
        
        $this->assertNotNull($setNodeExecution);
        
        // Verify that the set operation worked
        $output = $setNodeExecution->output_data;
        $this->assertIsArray($output);
        $this->assertArrayHasKey('new_field', $output);
        $this->assertEquals('processed_test', $output['new_field']);
        
        echo "✅ Data flow validation passed\n";
    }

    public function test_conditional_data_flow(): void
    {
        $user = \App\Models\User::factory()->create();
        
        // Create a workflow with conditional branching
        $workflow = Workflow::create([
            'name' => 'Conditional Flow Test',
            'nodes' => [
                [
                    'id' => 'trigger_1',
                    'type' => 'manual_trigger',
                    'name' => 'Manual Trigger',
                    'parameters' => []
                ],
                [
                    'id' => 'if_1',
                    'type' => 'if',
                    'name' => 'If Node',
                    'parameters' => [
                        'conditions' => [
                            [
                                'value1' => '{{$json.number}}',
                                'operation' => 'greater_than',
                                'value2' => '10'
                            ]
                        ]
                    ]
                ],
                [
                    'id' => 'true_branch',
                    'type' => 'set',
                    'name' => 'True Branch',
                    'parameters' => [
                        'values' => [
                            [
                                'name' => 'result',
                                'setTo' => 'greater_than_10'
                            ]
                        ]
                    ]
                ],
                [
                    'id' => 'false_branch',
                    'type' => 'set', 
                    'name' => 'False Branch',
                    'parameters' => [
                        'values' => [
                            [
                                'name' => 'result',
                                'setTo' => 'less_or_equal_10'
                            ]
                        ]
                    ]
                ]
            ],
            'connections' => [
                [
                    'source' => 'trigger_1',
                    'target' => 'if_1'
                ],
                [
                    'source' => 'if_1',
                    'target' => 'true_branch',
                    'sourceOutput' => 'true'
                ],
                [
                    'source' => 'if_1',
                    'target' => 'false_branch',
                    'sourceOutput' => 'false'
                ]
            ],
            'active' => true,
            'user_id' => $user->id
        ]);

        // Test the true branch (number > 10)
        $executor = app(WorkflowExecutor::class);
        $execution1 = $executor->execute($workflow, ['number' => 15], 'manual');
        $this->assertEquals('success', $execution1->fresh()->status);
        
        // Test the false branch (number <= 10)
        $execution2 = $executor->execute($workflow, ['number' => 5], 'manual');
        $this->assertEquals('success', $execution2->fresh()->status);
        
        echo "✅ Conditional data flow validation passed\n";
    }

    public function test_context_data_access(): void
    {
        $user = \App\Models\User::factory()->create();
        
        // Test the ExecutionContext service directly
        $workflow = Workflow::create([
            'name' => 'Context Test',
            'nodes' => [
                [
                    'id' => 'trigger_1',
                    'type' => 'manual_trigger',
                    'name' => 'Manual Trigger',
                    'parameters' => []
                ]
            ],
            'connections' => [],
            'active' => true,
            'user_id' => $user->id
        ]);

        // Create an execution and execution data to test context building
        $execution = $workflow->executions()->create([
            'status' => 'running',
            'mode' => 'manual',
            'input_data' => ['test' => 'value']
        ]);

        // Create some execution data to be used in context
        $execution->executionData()->create([
            'node_id' => 'some_node_id',
            'node_name' => 'Some Node',
            'node_type' => 'http_request',
            'status' => 'success',
            'output_data' => ['api_result' => 'success', 'data' => ['id' => 123]]
        ]);

        // Test context building
        $contextService = app(ExecutionContext::class);
        $context = $contextService->build($execution, collect($workflow->nodes)->keyBy('id'));

        // Validate the context structure
        $this->assertArrayHasKey('execution', $context);
        $this->assertArrayHasKey('workflow', $context);
        $this->assertArrayHasKey('nodes', $context);
        
        // Validate node data is accessible in context
        $this->assertArrayHasKey('some_node_id', $context['nodes']);
        $this->assertEquals(['api_result' => 'success', 'data' => ['id' => 123]], $context['nodes']['some_node_id']['json']);
        
        echo "✅ Context data access validation passed\n";
    }
}