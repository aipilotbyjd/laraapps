<?php

namespace App\Nodes\Transform;

use App\Nodes\Base\BaseNode;

class Code extends BaseNode
{
    protected string $name = 'Code';
    protected string $type = 'code';
    protected string $group = 'transform';
    
    public function execute(array $input, array $parameters, array $credentials = []): array
    {
        $code = $parameters['code'] ?? '';
        $mode = $parameters['mode'] ?? 'run_once';
        
        if ($mode === 'run_once') {
            return $this->executeOnce($code, $input);
        }
        
        return $this->executeForEach($code, $input);
    }
    
    protected function executeOnce(string $code, array $input): array
    {
        // Use V8Js extension if available, otherwise sandbox PHP
        if (extension_loaded('v8js')) {
            $v8 = new \V8Js();
            $v8->items = $input;
            
            try {
                $result = $v8->executeString($code);
                return is_array($result) ? $result : ['result' => $result];
            } catch (\V8JsException $e) {
                throw new \Exception('JavaScript execution error: ' . $e->getMessage());
            }
        }
        
        // Fallback: Use a sandboxed PHP eval (not recommended for production)
        // Better: Use a proper sandboxing solution or external JS runtime
        throw new \Exception('V8Js extension not available. Install v8js for Code node.');
    }
    
    protected function executeForEach(string $code, array $input): array
    {
        $results = [];
        
        foreach ($input as $item) {
            if (extension_loaded('v8js')) {
                $v8 = new \V8Js();
                $v8->item = $item;
                
                try {
                    $result = $v8->executeString($code);
                    $results[] = is_array($result) ? $result : ['result' => $result];
                } catch (\V8JsException $e) {
                    $results[] = ['error' => $e->getMessage()];
                }
            }
        }
        
        return $results;
    }
    
    protected function getDescription(): string
    {
        return 'Execute custom JavaScript code';
    }
    
    protected function getProperties(): array
    {
        return [
            [
                'name' => 'mode',
                'displayName' => 'Mode',
                'type' => 'select',
                'default' => 'run_once',
                'options' => [
                    ['name' => 'Run Once for All Items', 'value' => 'run_once'],
                    ['name' => 'Run Once for Each Item', 'value' => 'each_item'],
                ],
            ],
            [
                'name' => 'code',
                'displayName' => 'JavaScript Code',
                'type' => 'code',
                'required' => true,
                'default' => '// Your code here\nreturn items;',
            ],
        ];
    }
}