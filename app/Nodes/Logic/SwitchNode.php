<?php

namespace App\Nodes\Logic;

use App\Nodes\Base\BaseNode;

class SwitchNode extends BaseNode
{
    protected string $name = 'Switch';
    protected string $type = 'switch';
    protected string $group = 'logic';
    protected array $outputs = ['default']; // Dynamic outputs based on rules
    
    public function execute(array $input, array $parameters, array $credentials = []): array
    {
        $value = $this->resolveParameter($parameters['value'], $input);
        $rules = $parameters['rules'] ?? [];
        
        foreach ($rules as $rule) {
            $ruleValue = $this->resolveParameter($rule['value'], $input);
            $operation = $rule['operation'];
            
            if ($this->evaluateCondition($value, $operation, $ruleValue)) {
                return [
                    'output' => $rule['output'],
                    'data' => $input
                ];
            }
        }
        
        return [
            'output' => 'default',
            'data' => $input
        ];
    }
    
    protected function evaluateCondition($value1, $operation, $value2): bool
    {
        return match($operation) {
            'equals' => $value1 == $value2,
            'not_equals' => $value1 != $value2,
            'contains' => str_contains((string)$value1, (string)$value2),
            'not_contains' => !str_contains((string)$value1, (string)$value2),
            'starts_with' => str_starts_with((string)$value1, (string)$value2),
            'ends_with' => str_ends_with((string)$value1, (string)$value2),
            'greater_than' => $value1 > $value2,
            'less_than' => $value1 < $value2,
            'greater_or_equal' => $value1 >= $value2,
            'less_or_equal' => $value1 <= $value2,
            'is_empty' => empty($value1),
            'is_not_empty' => !empty($value1),
            'regex_match' => preg_match($value2, $value1),
            default => false
        };
    }
    
    protected function getDescription(): string
    {
        return 'Route data based on switch conditions';
    }
    
    protected function getProperties(): array
    {
        return [
            [
                'name' => 'value',
                'displayName' => 'Value to Match',
                'type' => 'string',
                'required' => true,
                'placeholder' => 'e.g. {{ $json.status }}',
            ],
            [
                'name' => 'rules',
                'displayName' => 'Rules',
                'type' => 'collection',
                'default' => [],
                'options' => [
                    [
                        'name' => 'output',
                        'displayName' => 'Output Name',
                        'type' => 'string',
                    ],
                    [
                        'name' => 'operation',
                        'displayName' => 'Operation',
                        'type' => 'select',
                        'options' => [
                            ['name' => 'Equals', 'value' => 'equals'],
                            ['name' => 'Not Equals', 'value' => 'not_equals'],
                            ['name' => 'Contains', 'value' => 'contains'],
                            ['name' => 'Greater Than', 'value' => 'greater_than'],
                            ['name' => 'Less Than', 'value' => 'less_than'],
                            ['name' => 'Is Empty', 'value' => 'is_empty'],
                        ],
                    ],
                    [
                        'name' => 'value',
                        'displayName' => 'Compare To',
                        'type' => 'string',
                    ],
                ],
            ],
        ];
    }
}