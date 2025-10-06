<?php

namespace App\Nodes\Triggers;

use App\Nodes\Base\BaseWebhook;
use App\Models\Workflow;
use App\Models\Webhook;
use Illuminate\Http\Request;

class WebhookTrigger extends BaseWebhook
{
    protected string $name = 'Webhook';
    protected string $type = 'webhook_trigger';
    protected string $group = 'trigger';
    
    public function execute(array $input, array $parameters, array $credentials = []): array
    {
        return $input;
    }
    
    public function register(Workflow $workflow, array $parameters): void
    {
        $node = collect($workflow->nodes)->firstWhere('type', $this->type);
        
        if (!$node) {
            return;
        }
        
        Webhook::updateOrCreate(
            [
                'workflow_id' => $workflow->id,
                'node_id' => $node['id'],
            ],
            [
                'method' => $parameters['method'] ?? 'POST',
                'response_mode' => $parameters['response_mode'] ?? ['mode' => 'last_node'],
                'active' => $workflow->active,
            ]
        );
    }
    
    public function unregister(Workflow $workflow): void
    {
        $workflow->webhooks()->delete();
    }
    
    public function getWebhookPath(): string
    {
        return '/webhook/' . uniqid();
    }
    
    public function processWebhookData(Request $request): array
    {
        return [
            'headers' => $request->headers->all(),
            'query' => $request->query(),
            'body' => $request->all(),
            'method' => $request->method(),
        ];
    }
    
    protected function getDescription(): string
    {
        return 'Triggers workflow when webhook receives HTTP request';
    }
    
    protected function getProperties(): array
    {
        return [
            [
                'name' => 'method',
                'displayName' => 'HTTP Method',
                'type' => 'select',
                'default' => 'POST',
                'options' => [
                    ['name' => 'GET', 'value' => 'GET'],
                    ['name' => 'POST', 'value' => 'POST'],
                    ['name' => 'PUT', 'value' => 'PUT'],
                    ['name' => 'DELETE', 'value' => 'DELETE'],
                ],
            ],
            [
                'name' => 'response_mode',
                'displayName' => 'Response Mode',
                'type' => 'select',
                'default' => 'last_node',
                'options' => [
                    ['name' => 'Respond Immediately', 'value' => 'immediate'],
                    ['name' => 'When Last Node Finishes', 'value' => 'last_node'],
                    ['name' => 'Wait for Webhook Node', 'value' => 'wait_for_webhook'],
                ],
            ],
        ];
    }
}