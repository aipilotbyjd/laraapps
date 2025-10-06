<?php

namespace App\Services\Expression;

class ExpressionParser
{
    protected array $functions = [];
    
    public function __construct()
    {
        $this->registerDefaultFunctions();
    }
    
    public function parse(string $expression, array $context): mixed
    {
        // Match {{ }} expressions
        return preg_replace_callback('/\{\{(.+?)\}\}/', function ($matches) use ($context) {
            $expr = trim($matches[1]);
            return $this->evaluate($expr, $context);
        }, $expression);
    }
    
    protected function evaluate(string $expression, array $context): mixed
    {
        // Handle $json references
        if (str_starts_with($expression, '$json')) {
            return $this->resolveJsonPath($expression, $context);
        }
        
        // Handle $node references
        if (str_starts_with($expression, '$node')) {
            return $this->resolveNodeReference($expression, $context);
        }
        
        // Handle functions
        if (preg_match('/^(\w+)\((.*)\)$/', $expression, $matches)) {
            $function = $matches[1];
            $args = $this->parseArguments($matches[2], $context);
            
            if (isset($this->functions[$function])) {
                return call_user_func_array($this->functions[$function], $args);
            }
        }
        
        // Return as-is if no pattern matched
        return $expression;
    }
    
    protected function resolveJsonPath(string $path, array $context): mixed
    {
        // Remove $json. prefix
        $path = substr($path, 6);
        
        // Try to get from the special 'json' context which contains current node input data
        if (isset($context['json']) && is_array($context['json'])) {
            $jsonResult = data_get($context['json'], $path);
            if ($jsonResult !== null) {
                return $jsonResult;
            }
        }
        
        // Then try direct access in the context root (for backward compatibility)
        if (array_key_exists($path, $context)) {
            return $context[$path];
        }
        
        // Then try data_get for nested properties
        $result = data_get($context, $path);
        if ($result !== null && $result !== $context) { // data_get returns original array if key not found
            return $result;
        }
        
        // Try to get from nodes context (for referencing previous node data) 
        $nodeResult = data_get($context, "nodes.current.json.{$path}");
        if ($nodeResult !== null && $nodeResult !== $context) {
            return $nodeResult;
        }
        
        // If all else fails, return null
        return null;
    }
    
    protected function resolveNodeReference(string $reference, array $context): mixed
    {
        // Example: $node["NodeName"].json.field
        preg_match('/\$node\["(.+?)"\]\.json\.(.+)/', $reference, $matches);
        
        if (count($matches) === 3) {
            $nodeName = $matches[1];
            $field = $matches[2];
            
            // Find node by name in context
            foreach ($context['nodes'] ?? [] as $nodeId => $nodeData) {
                // Match by node name if available
                return data_get($nodeData, "json.{$field}");
            }
        }
        
        return null;
    }
    
    protected function parseArguments(string $args, array $context): array
    {
        if (empty(trim($args))) {
            return [];
        }
        
        $arguments = explode(',', $args);
        $parsed = [];
        
        foreach ($arguments as $arg) {
            $arg = trim($arg);
            
            // Remove quotes if present
            if (preg_match('/^["\'](.+)["\']$/', $arg, $matches)) {
                $parsed[] = $matches[1];
            } elseif (is_numeric($arg)) {
                $parsed[] = $arg + 0;
            } else {
                $parsed[] = $this->evaluate($arg, $context);
            }
        }
        
        return $parsed;
    }
    
    protected function registerDefaultFunctions(): void
    {
        $this->functions['now'] = fn() => now()->toIso8601String();
        $this->functions['today'] = fn() => now()->toDateString();
        $this->functions['uuid'] = fn() => \Illuminate\Support\Str::uuid();
        $this->functions['random'] = fn($min = 0, $max = 100) => rand($min, $max);
        $this->functions['upper'] = fn($str) => strtoupper($str);
        $this->functions['lower'] = fn($str) => strtolower($str);
        $this->functions['length'] = fn($str) => strlen($str);
        $this->functions['trim'] = fn($str) => trim($str);
        $this->functions['replace'] = fn($str, $search, $replace) => str_replace($search, $replace, $str);
        $this->functions['split'] = fn($str, $delimiter) => explode($delimiter, $str);
        $this->functions['join'] = fn($array, $glue) => implode($glue, $array);
    }
    
    public function registerFunction(string $name, callable $callback): void
    {
        $this->functions[$name] = $callback;
    }
}