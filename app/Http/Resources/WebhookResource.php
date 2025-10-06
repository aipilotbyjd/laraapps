<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WebhookResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'workflow_id' => $this->workflow_id,
            'node_id' => $this->node_id,
            'webhook_id' => $this->webhook_id,
            'path' => $this->path,
            'method' => $this->method,
            'active' => $this->active,
            'response_mode' => $this->response_mode,
            'request_count' => $this->request_count,
            'last_called_at' => $this->last_called_at,
            'full_url' => $this->full_url,
            'created_at' => $this->created_at,
        ];
    }
}