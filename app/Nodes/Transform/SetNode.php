<?php

namespace App\Nodes\Transform;

use App\Nodes\Base\BaseNode;

class SetNode extends BaseNode
{
    protected string $name = 'Set';
    protected string $type = 'set';
    protected string $group = 'transform';
    
    public function execute(array $input, array $parameters, array $credentials = []): array
    {
        $output = $input;
        
        foreach ($parameters['values'] ?? [] as $value) {
            $name = $value['name'];
            $setTo = $this->resolveParameter($value['setTo'], $input);
            
            // Use data_set to set the value in the output array
            $output = $this->setDataValue($output, $name, $setTo);
        }
        
        return $output;
    }
    
    private function setDataValue(array $array, string $key, mixed $value): array
    {
        $keys = explode('.', $key);
        $current = &$array;
        
        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                $current[$k] = $value;
            } else {
                if (!isset($current[$k]) || !is_array($current[$k])) {
                    $current[$k] = [];
                }
                $current = &$current[$k];
            }
        }
        
        return $array;
    }
    
    protected function getDescription(): string
    {
        return 'Set values in workflow data';
    }
    
    protected function getProperties(): array
    {
        return [
            [
                'name' => 'values',
                'displayName' => 'Values to Set',
                'type' => 'collection',
                'default' => [],
                'options' => [
                    [
                        'name' => 'name',
                        'displayName' => 'Name',
                        'type' => 'string',
                        'placeholder' => 'e.g. myVariable',
                    ],
                    [
                        'name' => 'setTo',
                        'displayName' => 'Set To',
                        'type' => 'string',
                        'placeholder' => 'e.g. {{ $json.input }}',
                    ],
                ],
            ],
        ];
    }
}