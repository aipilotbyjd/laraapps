<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handle(Request $request, string $webhookId)
    {
        $webhook = Webhook::where('webhook_id', $webhookId)
            ->where('active', true)
            ->firstOrFail();

        if (!$webhook->workflow->active) {
            return response()->json(['error' => 'Workflow is not active'], 400);
        }

        // Validate method
        if (strtoupper($request->method()) !== $webhook->method) {
            return response()->json(['error' => 'Method not allowed'], 405);
        }

        // Increment request count
        $webhook->incrementRequestCount();

        // Process webhook data through the trigger node
        $webhookTrigger = $webhook->workflow->getNodeById($webhook->node_id);
        
        if (!$webhookTrigger) {
            return response()->json(['error' => 'Webhook trigger node not found'], 500);
        }

        // Prepare input data
        $inputData = [
            'headers' => $request->headers->all(),
            'query' => $request->query(),
            'body' => $request->all(),
            'method' => $request->method(),
        ];

        // Execute the workflow
        $execution = app('workflow.executor')->execute(
            $webhook->workflow, 
            $inputData, 
            'webhook'
        );

        // Handle response based on webhook response mode
        $responseMode = $webhook->response_mode['mode'] ?? 'last_node';
        
        if ($responseMode === 'immediate') {
            return response()->json(['status' => 'received', 'execution_id' => $execution->id]);
        }

        // For other modes, return a response that reflects the final execution result
        if ($execution->status === 'success') {
            return response()->json($execution->output_data ?? ['status' => 'success']);
        } else {
            return response()->json(['error' => $execution->error_message], 500);
        }
    }
}