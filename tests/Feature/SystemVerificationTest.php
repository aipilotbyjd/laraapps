<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Workflow;
use App\Services\Node\NodeRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SystemVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_components_are_properly_registered(): void
    {
        // Test Node Registry
        $registry = app(NodeRegistry::class);
        $this->assertNotNull($registry);
        
        // Test that key nodes are registered
        $nodesToTest = [
            'webhook_trigger',
            'schedule_trigger', 
            'manual_trigger',
            'http_request',
            'if',
            'wait',
            'code',
            'set'
        ];
        
        foreach ($nodesToTest as $nodeType) {
            $node = $registry->getNode($nodeType);
            $this->assertNotNull($node, "Node {$nodeType} should be registered");
        }
        
        // Test workflow creation
        $workflow = Workflow::create([
            'name' => 'Test Workflow',
            'nodes' => [
                [
                    'id' => 'node1',
                    'type' => 'manual_trigger',
                    'parameters' => []
                ]
            ],
            'connections' => [],
            'active' => true,
            'user_id' => 1
        ]);
        
        $this->assertNotNull($workflow);
        $this->assertEquals('Test Workflow', $workflow->name);
        
        echo "✅ All system components verified successfully!\n";
    }
}