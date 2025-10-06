<?php

namespace App\Services\Execution;

use App\Models\Workflow;
use App\Models\Execution;
use App\Models\ExecutionData;
use App\Models\WaitingExecution;
use App\Services\Node\NodeRegistry;
use App\Events\WorkflowExecutionStarted;
use App\Events\WorkflowExecutionCompleted;
use App\Events\WorkflowExecutionFailed;
use Illuminate\Support\Facades\DB;

class WorkflowExecutor
{
    public function __construct(
        protected NodeRegistry $nodeRegistry,
        protected NodeExecutor $nodeExecutor,
        protected ExecutionContext $context,
        protected ExecutionLogger $logger
    ) {}
    
    public function execute(Workflow $workflow, array $inputData = [], string $mode = 'manual'): Execution
    {
        $execution = $this->createExecution($workflow, $inputData, $mode);
        
        event(new WorkflowExecutionStarted($execution));
        
        try {
            DB::beginTransaction();
            
            $execution->markAsRunning();
            
            // Build execution graph
            $graph = $this->buildGraph($workflow);
            $nodes = collect($workflow->nodes)->keyBy('id');
            
            // Find starting node
            $startNode = $this->findStartNode($nodes);
            
            if (!$startNode) {
                throw new \Exception('No trigger node found in workflow');
            }
            
            // Execute workflow
            $result = $this->executeGraph(
                $execution,
                $graph,
                $startNode,
                $inputData,
                $nodes
            );
            
            // Check if workflow is waiting
            if (isset($result['wait']) && $result['wait']) {
                $this->handleWaitState($execution, $result);
            } else {
                $execution->markAsSuccess($result);
                event(new WorkflowExecutionCompleted($execution));
            }
            
            $workflow->incrementExecutionCount();
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $execution->markAsFailed($e->getMessage());
            event(new WorkflowExecutionFailed($execution));
            
            $this->logger->logError($execution, $e);
        }
        
        return $execution->fresh();
    }
    
    protected function createExecution(Workflow $workflow, array $inputData, string $mode): Execution
    {
        return Execution::create([
            'workflow_id' => $workflow->id,
            'mode' => $mode,
            'input_data' => $inputData,
            'status' => 'waiting',
        ]);
    }
    
    protected function buildGraph(Workflow $workflow): array
    {
        $graph = [];
        
        foreach ($workflow->connections as $connection) {
            $source = $connection['source'];
            $target = $connection['target'];
            $sourceOutput = $connection['sourceOutput'] ?? 'main';
            
            if (!isset($graph[$source])) {
                $graph[$source] = [];
            }
            
            $graph[$source][] = [
                'target' => $target,
                'output' => $sourceOutput,
            ];
        }
        
        return $graph;
    }
    
    protected function findStartNode($nodes)
    {
        // Find trigger node
        return $nodes->first(function ($node) {
            return str_contains($node['type'], 'trigger') || 
                   str_contains($node['type'], 'webhook');
        });
    }
    
    protected function executeGraph(
        Execution $execution,
        array $graph,
        array $currentNode,
        array $data,
        $allNodes
    ): array {
        $nodeId = $currentNode['id'];
        
        // Log node execution start
        $nodeExecution = $this->logger->logNodeStart($execution, $currentNode, $data);
        
        try {
            // Execute current node
            $output = $this->nodeExecutor->execute(
                $currentNode,
                $data,
                $this->context->build($execution, $allNodes)
            );
            
            // Log node success
            $this->logger->logNodeSuccess($nodeExecution, $output);
            
            // Check if this is a wait node
            if (isset($output['wait']) && $output['wait']) {
                // Add node_id to the output for wait state tracking
                $output['node_id'] = $nodeId;
                return $output;
            }
            
            // Handle conditional outputs (like If node)
            if (isset($output['output'])) {
                $outputKey = $output['output'];
                $data = $output['data'];
            } else {
                $outputKey = 'main';
                $data = $output;
            }
            
            // Find and execute next nodes
            if (isset($graph[$nodeId])) {
                foreach ($graph[$nodeId] as $connection) {
                    // Check if output matches
                    if ($connection['output'] === $outputKey || $connection['output'] === 'main') {
                        $nextNode = $allNodes->get($connection['target']);
                        
                        if ($nextNode) {
                            return $this->executeGraph(
                                $execution,
                                $graph,
                                $nextNode,
                                $data,
                                $allNodes
                            );
                        }
                    }
                }
            }
            
            return $data;
            
        } catch (\Exception $e) {
            $this->logger->logNodeError($nodeExecution, $e->getMessage());
            throw $e;
        }
    }
    
    protected function handleWaitState(Execution $execution, array $result)
    {
        // We need to find the node_id from the execution data since it's not in the result
        $lastNodeExecution = $execution->executionData()->latest()->first();
        $nodeId = $lastNodeExecution ? $lastNodeExecution->node_id : null;
        
        $execution->update([
            'status' => 'waiting',
            'waiting_till' => $result['resume_at'] ?? null,
        ]);
        
        WaitingExecution::create([
            'execution_id' => $execution->id,
            'node_id' => $result['node_id'] ?? $nodeId,
            'wait_type' => $result['wait_type'] ?? 'time',
            'resume_at' => $result['resume_at'] ?? null,
            'context_data' => $result['data'] ?? [],
        ]);
    }
    
    public function resume(Execution $execution, array $resumeData = []): Execution
    {
        $waitingExecution = $execution->waitingExecution;
        
        if (!$waitingExecution) {
            throw new \Exception('Execution is not in waiting state');
        }
        
        $workflow = $execution->workflow;
        $contextData = $waitingExecution->context_data;
        
        // Merge resume data
        $data = array_merge($contextData, $resumeData);
        
        // Delete wait record and continue with original execute logic
        $waitingExecution->delete();
        
        // Mark as running again
        $execution->update([
            'status' => 'running',
            'started_at' => now(),
        ]);
        
        // Continue the execution using the original execute method but with existing execution
        try {
            $graph = $this->buildGraph($workflow);
            $nodes = collect($workflow->nodes)->keyBy('id');
            
            // Find starting point - we need to determine where to resume from
            // This is complex, so simplest is to start from the trigger, but with the saved context data
            $startNode = $this->findStartNode($nodes);
            
            if (!$startNode) {
                throw new \Exception('No trigger node found in workflow');
            }
            
            $result = $this->executeGraph(
                $execution,
                $graph,
                $startNode,
                $data, // Use the context/resume data
                $nodes
            );
            
            if (isset($result['wait']) && $result['wait']) {
                $this->handleWaitState($execution, $result);
            } else {
                $execution->markAsSuccess($result);
                event(new WorkflowExecutionCompleted($execution));
            }
            
            $workflow->incrementExecutionCount();
            
        } catch (\Exception $e) {
            $execution->markAsFailed($e->getMessage());
            event(new WorkflowExecutionFailed($execution));
            $this->logger->logError($execution, $e);
        }
        
        return $execution->fresh();
    }
}