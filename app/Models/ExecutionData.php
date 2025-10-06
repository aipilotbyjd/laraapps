<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExecutionData extends Model
{
    protected $fillable = [
        'execution_id', 'node_id', 'node_name', 'node_type',
        'status', 'input_data', 'output_data', 'error',
        'started_at', 'finished_at', 'duration_ms'
    ];
    
    protected $casts = [
        'input_data' => 'array',
        'output_data' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function execution(): BelongsTo
    {
        return $this->belongsTo(Execution::class);
    }
}