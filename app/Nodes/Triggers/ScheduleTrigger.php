<?php

namespace App\Nodes\Triggers;

use App\Nodes\Base\BaseTrigger;
use App\Models\Workflow;
use App\Models\Schedule;

class ScheduleTrigger extends BaseTrigger
{
    protected string $name = 'Schedule';
    protected string $type = 'schedule_trigger';
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
        
        Schedule::updateOrCreate(
            [
                'workflow_id' => $workflow->id,
                'node_id' => $node['id'],
            ],
            [
                'cron_expression' => $parameters['cron_expression'] ?? '* * * * *',
                'timezone' => $parameters['timezone'] ?? 'UTC',
                'active' => $workflow->active,
            ]
        );
    }
    
    public function unregister(Workflow $workflow): void
    {
        $workflow->schedules()->delete();
    }
    
    protected function getDescription(): string
    {
        return 'Triggers workflow on a schedule';
    }
    
    protected function getProperties(): array
    {
        return [
            [
                'name' => 'cron_expression',
                'displayName' => 'Cron Expression',
                'type' => 'string',
                'default' => '* * * * *',
                'description' => 'Cron expression for scheduling (e.g., "* * * * *" for every minute)',
            ],
            [
                'name' => 'timezone',
                'displayName' => 'Timezone',
                'type' => 'string',
                'default' => 'UTC',
            ],
        ];
    }
}