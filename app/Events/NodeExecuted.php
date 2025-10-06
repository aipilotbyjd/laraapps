<?php

namespace App\Events;

use App\Models\ExecutionData;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NodeExecuted
{
    use Dispatchable, SerializesModels;
    
    public function __construct(public ExecutionData $executionData) {}
}