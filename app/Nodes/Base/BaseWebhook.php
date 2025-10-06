<?php

namespace App\Nodes\Base;

use App\Models\Workflow;
use Illuminate\Http\Request;

abstract class BaseWebhook extends BaseTrigger
{
    abstract public function getWebhookPath(): string;
    
    abstract public function processWebhookData(Request $request): array;
}