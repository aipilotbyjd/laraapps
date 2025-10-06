<?php

namespace App\Nodes\Base;

use App\Models\Workflow;

abstract class BaseTrigger extends BaseNode
{
    protected array $inputs = [];
    
    abstract public function register(Workflow $workflow, array $parameters): void;
    
    abstract public function unregister(Workflow $workflow): void;
}