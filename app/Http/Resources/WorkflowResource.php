<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'active' => $this->active,
            'nodes' => $this->nodes,
            'connections' => $this->connections,
            'settings' => $this->settings,
            'static_data' => $this->static_data,
            'execution_count' => $this->execution_count,
            'last_executed_at' => $this->last_executed_at,
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'webhooks' => WebhookResource::collection($this->whenLoaded('webhooks')),
            'schedules' => ScheduleResource::collection($this->whenLoaded('schedules')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}