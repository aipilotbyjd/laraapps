<?php

namespace App\Nodes\Base;

use App\Nodes\Contracts\NodeInterface;
use App\Services\Expression\ExpressionParser;

abstract class BaseNode implements NodeInterface
{
    protected string $name;
    protected string $type;
    protected string $group;
    protected array $inputs = ['main'];
    protected array $outputs = ['main'];
    
    abstract public function execute(array $input, array $parameters, array $credentials = []): array;
    
    public function getDefinition(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'group' => $this->group,
            'version' => 1,
            'description' => $this->getDescription(),
            'inputs' => $this->inputs,
            'outputs' => $this->outputs,
            'properties' => $this->getProperties(),
            'credentials' => $this->getCredentials(),
        ];
    }
    
    abstract protected function getDescription(): string;
    
    abstract protected function getProperties(): array;
    
    protected function getCredentials(): array
    {
        return [];
    }
    
    public function validate(array $parameters): bool
    {
        $properties = $this->getProperties();
        
        foreach ($properties as $property) {
            if (($property['required'] ?? false) && empty($parameters[$property['name']])) {
                throw new \Exception("Missing required parameter: {$property['name']}");
            }
        }
        
        return true;
    }
    
    protected function resolveParameter($value, array $context)
    {
        if (is_string($value) && str_contains($value, '{{')) {
            return app(ExpressionParser::class)->parse($value, $context);
        }
        
        return $value;
    }
}