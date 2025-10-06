<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExecutionDataResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'node_id' => $this->node_id,
            'node_name' => $this->node_name,
            'node_type' => $this->node_type,
            'status' => $this->status,
            'input_data' => $this->input_data,
            'output_data' => $this->output_data,
            'error' => $this->error,
            'started_at' => $this->started_at,
            'finished_at' => $this->finished_at,
            'duration_ms' => $this->duration_ms,
        ];
    }
}