<?php

namespace App\Nodes\Contracts;

interface TriggerInterface extends NodeInterface
{
    public function register(Workflow $workflow, array $parameters): void;
    
    public function unregister(Workflow $workflow): void;
}