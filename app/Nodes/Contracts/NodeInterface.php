<?php

namespace App\Nodes\Contracts;

interface NodeInterface
{
    public function execute(array $input, array $parameters, array $credentials = []): array;
    
    public function getDefinition(): array;
    
    public function validate(array $parameters): bool;
}