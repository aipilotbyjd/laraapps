<?php

namespace App\Nodes\Logic;

use App\Nodes\Base\BaseNode;

class IfNode extends BaseNode
{
    protected string $name = 'If';
    protected string $type = 'if';
    protected string $group = 'logic';
    protected array $outputs = ['true', 'false'];
    
    public function execute(array $input, array $parameters, array $credentials = []): array
    {
        $conditions = $parameters['conditions'] ?? [];
        $combineOperation = $parameters['combine_operation'] ?? 'and';
        
        $results = [];
        
        foreach ($conditions as $condition) {
            $value1 = $this->resolveParameter($condition['value1'], $input);
            $value2 = $this->resolveParameter($condition['value2'], $input);
            $operation = $condition['operation'];
            
            $results[] = $this->evaluateCondition($value1, $operation, $value2);
        }
        
        $finalResult = $combineOperation === 'and' 
            ? !in_array(false, $results) 
            : in_array(true, $results);
        
        return [
            'output' => $finalResult ? 'true' : 'false',
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
        return 'Split workflow based on conditions';
    }
    
    protected function getProperties(): array
    {
        return [
            [
                'name' => 'conditions',
                'displayName' => 'Conditions',
                'type' => 'collection',
                'required' => true,
                'default' => [],
                'options' => [
                    [
                        'name' => 'value1',
                        'displayName' => 'Value 1',
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
                            ['name' => 'Not Contains', 'value' => 'not_contains'],
                            ['name' => 'Greater Than', 'value' => 'greater_than'],
                            ['name' => 'Less Than', 'value' => 'less_than'],
                            ['name' => 'Is Empty', 'value' => 'is_empty'],
                            ['name' => 'Regex Match', 'value' => 'regex_match'],
                        ],
                    ],
                    [
                        'name' => 'value2',
                        'displayName' => 'Value 2',
                        'type' => 'string',
                    ],
                ],
            ],
            [
                'name' => 'combine_operation',
                'displayName' => 'Combine',
                'type' => 'select',
                'default' => 'and',
                'options' => [
                    ['name' => 'AND', 'value' => 'and'],
                    ['name' => 'OR', 'value' => 'or'],
                ],
            ],
        ];
    }
}