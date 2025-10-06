<?php

namespace App\Services\Execution;

use App\Services\Node\NodeRegistry;
use App\Services\Credential\CredentialService;
use App\Services\Expression\ExpressionParser;

class NodeExecutor
{
    public function __construct(
        protected NodeRegistry $nodeRegistry,
        protected CredentialService $credentialService,
        protected ExpressionParser $expressionParser
    ) {}
    
    public function execute(array $nodeConfig, array $inputData, array $context): array
    {
        $nodeType = $nodeConfig['type'];
        $parameters = $nodeConfig['parameters'] ?? [];
        
        // Get node instance
        $node = $this->nodeRegistry->getNode($nodeType);
        
        // Validate parameters
        $node->validate($parameters);
        
        // Create context with current input data for $json expressions
        // Add the current input as $json context so expressions like {{$json.field}} work
        $parameterContext = $context;
        $parameterContext['json'] = $inputData; // Make input data available as $json
        
        // Resolve expressions in parameters
        $resolvedParameters = $this->resolveParameters($parameters, $parameterContext);
        
        // Get credentials if needed
        $credentials = [];
        if (isset($nodeConfig['credentials']) && !empty($nodeConfig['credentials'])) {
            $credentialId = $nodeConfig['credentials'];
            if (is_numeric($credentialId)) {
                $credentials = $this->credentialService->get((int)$credentialId);
            }
        }
        
        // Execute node
        return $node->execute($inputData, $resolvedParameters, $credentials);
    }
    
    protected function resolveParameters(array $parameters, array $context): array
    {
        $resolved = [];
        
        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                $resolved[$key] = $this->resolveParameters($value, $context);
            } elseif (is_string($value) && str_contains($value, '{{')) {
                $resolved[$key] = $this->expressionParser->parse($value, $context);
            } else {
                $resolved[$key] = $value;
            }
        }
        
        return $resolved;
    }
}