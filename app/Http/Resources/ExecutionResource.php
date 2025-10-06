<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExecutionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'workflow_id' => $this->workflow_id,
            'workflow_name' => $this->workflow->name,
            'status' => $this->status,
            'mode' => $this->mode,
            'input_data' => $this->input_data,
            'output_data' => $this->output_data,
            'error_message' => $this->error_message,
            'retry_count' => $this->retry_count,
            'started_at' => $this->started_at,
            'finished_at' => $this->finished_at,
            'waiting_till' => $this->waiting_till,
            'duration_ms' => $this->duration_ms,
            'execution_data' => ExecutionDataResource::collection($this->whenLoaded('executionData')),
            'created_at' => $this->created_at,
        ];
    }
}