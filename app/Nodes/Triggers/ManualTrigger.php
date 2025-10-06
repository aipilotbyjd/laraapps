<?php

namespace App\Nodes\Triggers;

use App\Nodes\Base\BaseTrigger;
use App\Models\Workflow;

class ManualTrigger extends BaseTrigger
{
    protected string $name = 'Manual';
    protected string $type = 'manual_trigger';
    protected string $group = 'trigger';
    
    public function execute(array $input, array $parameters, array $credentials = []): array
    {
        return $input;
    }
    
    public function register(Workflow $workflow, array $parameters): void
    {
        // Manual triggers don't need registration
    }
    
    public function unregister(Workflow $workflow): void
    {
        // Manual triggers don't need unregistration
    }
    
    protected function getDescription(): string
    {
        return 'Starts workflow manually';
    }
    
    protected function getProperties(): array
    {
        return [
            [
                'name' => 'description',
                'displayName' => 'Description',
                'type' => 'string',
                'default' => 'Manual trigger to start workflow',
            ],
        ];
    }
}