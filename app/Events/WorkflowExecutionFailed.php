<?php

namespace App\Events;

use App\Models\Execution;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkflowExecutionFailed
{
    use Dispatchable, SerializesModels;
    
    public function __construct(public Execution $execution) {}
}