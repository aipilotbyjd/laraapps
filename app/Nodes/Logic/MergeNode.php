<?php

namespace App\Nodes\Logic;

use App\Nodes\Base\BaseNode;

class MergeNode extends BaseNode
{
    protected string $name = 'Merge';
    protected string $type = 'merge';
    protected string $group = 'logic';
    protected array $inputs = ['input1', 'input2']; // Can be configured dynamically
    
    public function execute(array $input, array $parameters, array $credentials = []): array
    {
        $mode = $parameters['mode'] ?? 'append';
        
        return match($mode) {
            'append' => $this->mergeAppend($input),
            'combine' => $this->mergeCombine($input),
            'intersect' => $this->mergeIntersect($input),
            default => $input
        };
    }
    
    protected function mergeAppend(array $input): array
    {
        $result = [];
        
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $value);
            } else {
                $result[] = $value;
            }
        }
        
        return $result;
    }
    
    protected function mergeCombine(array $input): array
    {
        $result = [];
        
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $value);
            } else {
                $result[$key] = $value;
            }
        }
        
        return $result;
    }
    
    protected function mergeIntersect(array $input): array
    {
        if (empty($input)) {
            return [];
        }
        
        $arrays = array_filter($input, 'is_array');
        
        if (empty($arrays)) {
            return $input;
        }
        
        $result = array_shift($arrays);
        
        foreach ($arrays as $array) {
            $result = array_intersect($result, $array);
        }
        
        return $result;
    }
    
    protected function getDescription(): string
    {
        return 'Merge data from multiple inputs';
    }
    
    protected function getProperties(): array
    {
        return [
            [
                'name' => 'mode',
                'displayName' => 'Merge Mode',
                'type' => 'select',
                'default' => 'append',
                'options' => [
                    ['name' => 'Append', 'value' => 'append'],
                    ['name' => 'Combine', 'value' => 'combine'],
                    ['name' => 'Intersect', 'value' => 'intersect'],
                ],
            ],
            [
                'name' => 'inputs',
                'displayName' => 'Number of Inputs',
                'type' => 'number',
                'default' => 2,
                'min' => 2,
                'max' => 10,
            ],
        ];
    }
}