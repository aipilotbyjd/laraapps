<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaitingExecution extends Model
{
    protected $fillable = [
        'execution_id', 'node_id', 'wait_type',
        'resume_at', 'resume_data', 'context_data'
    ];
    
    protected $casts = [
        'resume_at' => 'datetime',
        'resume_data' => 'array',
        'context_data' => 'array',
    ];

    protected $attributes = [
        'wait_type' => 'time',
    ];

    public function execution(): BelongsTo
    {
        return $this->belongsTo(Execution::class);
    }
}