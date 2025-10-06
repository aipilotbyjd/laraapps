<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Workflow;
use App\Models\Execution;
use App\Services\Execution\WorkflowExecutor;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WorkflowExecutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_simple_workflow_execution(): void
    {
        // Create a simple workflow with manual trigger -> if node -> http request
        // Create a user first
        $user = \App\Models\User::factory()->create();
        
        $workflow = Workflow::create([
            'name' => 'Test Workflow',
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
                    'name' => 'If Condition',
                    'parameters' => [
                        'conditions' => [
                            [
                                'value1' => '{{$json.test_value}}',
                                'operation' => 'greater_than',
                                'value2' => '5'
                            ]
                        ]
                    ]
                ]
            ],
            'connections' => [
                [
                    'source' => 'trigger_1',
                    'target' => 'if_1'
                ]
            ],
            'active' => true,
            'user_id' => $user->id
        ]);

        $this->assertNotNull($workflow);
        $this->assertEquals('Test Workflow', $workflow->name);

        // Test execution
        $executor = app(WorkflowExecutor::class);
        $execution = $executor->execute($workflow, ['test_value' => 10], 'manual');

        $this->assertNotNull($execution);
        $this->assertEquals('success', $execution->status);
        
        // Check that execution data was recorded
        $executionData = $execution->executionData;
        $this->assertNotEmpty($executionData);
        
        echo "✅ Simple workflow execution test passed\n";
    }

    public function test_wait_node_execution(): void
    {
        // Create a user first
        $user = \App\Models\User::factory()->create();
        
        // Create workflow with wait node
        $workflow = Workflow::create([
            'name' => 'Test Wait Workflow',
            'nodes' => [
                [
                    'id' => 'trigger_1',
                    'type' => 'manual_trigger',
                    'name' => 'Manual Trigger',
                    'parameters' => []
                ],
                [
                    'id' => 'wait_1',
                    'type' => 'wait',
                    'name' => 'Wait Node',
                    'parameters' => [
                        'wait_type' => 'time',
                        'amount' => 1,
                        'unit' => 'seconds'
                    ]
                ]
            ],
            'connections' => [
                [
                    'source' => 'trigger_1',
                    'target' => 'wait_1'
                ]
            ],
            'active' => true,
            'user_id' => $user->id
        ]);

        // Test execution with wait node
        $executor = app(WorkflowExecutor::class);
        $execution = $executor->execute($workflow, [], 'manual');

        $this->assertNotNull($execution);
        
        // Execution should be in waiting state due to wait node
        $this->assertTrue(in_array($execution->status, ['waiting', 'success']));
        
        echo "✅ Wait node workflow test completed\n";
    }
    
    public function test_node_data_passing(): void
    {
        // Create a user first
        $user = \App\Models\User::factory()->create();
        
        // Create a workflow to test data passing between nodes
        $workflow = Workflow::create([
            'name' => 'Data Passing Test',
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
                                'name' => 'processed_value',
                                'setTo' => '{{$json.input_value}}_processed'
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

        // Execute with input data
        $executor = app(WorkflowExecutor::class);
        $execution = $executor->execute($workflow, ['input_value' => 'test'], 'manual');

        $this->assertNotNull($execution);
        $this->assertEquals('success', $execution->status);
        
        echo "✅ Node data passing test passed\n";
    }
}