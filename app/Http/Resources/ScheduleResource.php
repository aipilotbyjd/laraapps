<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'workflow_id' => $this->workflow_id,
            'node_id' => $this->node_id,
            'cron_expression' => $this->cron_expression,
            'timezone' => $this->timezone,
            'active' => $this->active,
            'last_run_at' => $this->last_run_at,
            'next_run_at' => $this->next_run_at,
            'run_count' => $this->run_count,
            'created_at' => $this->created_at,
        ];
    }
}