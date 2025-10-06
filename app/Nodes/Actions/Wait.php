<?php

namespace App\Nodes\Actions;

use App\Nodes\Base\BaseNode;
use App\Models\WaitingExecution;

class Wait extends BaseNode
{
    protected string $name = 'Wait';
    protected string $type = 'wait';
    protected string $group = 'action';
    
    public function execute(array $input, array $parameters, array $credentials = []): array
    {
        $waitType = $parameters['wait_type'] ?? 'time';
        
        return match($waitType) {
            'time' => $this->waitForTime($input, $parameters),
            'webhook' => $this->waitForWebhook($input, $parameters),
            'condition' => $this->waitForCondition($input, $parameters),
            default => $input
        };
    }
    
    protected function waitForTime(array $input, array $parameters): array
    {
        $amount = $parameters['amount'] ?? 1;
        $unit = $parameters['unit'] ?? 'hours';
        
        $resumeAt = match($unit) {
            'seconds' => now()->addSeconds($amount),
            'minutes' => now()->addMinutes($amount),
            'hours' => now()->addHours($amount),
            'days' => now()->addDays($amount),
            default => now()->addHours(1)
        };
        
        return [
            'wait' => true,
            'resume_at' => $resumeAt,
            'data' => $input,
        ];
    }
    
    protected function waitForWebhook(array $input, array $parameters): array
    {
        return [
            'wait' => true,
            'wait_type' => 'webhook',
            'data' => $input,
        ];
    }
    
    protected function waitForCondition(array $input, array $parameters): array
    {
        return [
            'wait' => true,
            'wait_type' => 'condition',
            'condition' => $parameters['condition'],
            'data' => $input,
        ];
    }
    
    protected function getDescription(): string
    {
        return 'Pause workflow execution';
    }
    
    protected function getProperties(): array
    {
        return [
            [
                'name' => 'wait_type',
                'displayName' => 'Wait Type',
                'type' => 'select',
                'default' => 'time',
                'options' => [
                    ['name' => 'Time', 'value' => 'time'],
                    ['name' => 'Webhook', 'value' => 'webhook'],
                    ['name' => 'Condition', 'value' => 'condition'],
                ],
            ],
            [
                'name' => 'amount',
                'displayName' => 'Amount',
                'type' => 'number',
                'default' => 1,
                'displayOptions' => [
                    'show' => ['wait_type' => ['time']]
                ],
            ],
            [
                'name' => 'unit',
                'displayName' => 'Unit',
                'type' => 'select',
                'default' => 'hours',
                'options' => [
                    ['name' => 'Seconds', 'value' => 'seconds'],
                    ['name' => 'Minutes', 'value' => 'minutes'],
                    ['name' => 'Hours', 'value' => 'hours'],
                    ['name' => 'Days', 'value' => 'days'],
                ],
                'displayOptions' => [
                    'show' => ['wait_type' => ['time']]
                ],
            ],
        ];
    }
}