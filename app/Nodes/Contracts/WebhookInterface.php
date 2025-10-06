<?php

namespace App\Nodes\Contracts;

interface WebhookInterface extends TriggerInterface
{
    public function getWebhookPath(): string;
    
    public function processWebhookData(Request $request): array;
}