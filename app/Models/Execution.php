<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Execution extends Model
{
    protected $fillable = [
        'workflow_id', 'status', 'mode', 'input_data',
        'output_data', 'error_message', 'error_stack',
        'retry_count', 'parent_execution_id',
        'started_at', 'finished_at', 'waiting_till', 'duration_ms'
    ];
    
    protected $casts = [
        'input_data' => 'array',
        'output_data' => 'array',
        'error_stack' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'waiting_till' => 'datetime',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function executionData(): HasMany
    {
        return $this->hasMany(ExecutionData::class);
    }

    public function parentExecution(): BelongsTo
    {
        return $this->belongsTo(Execution::class, 'parent_execution_id');
    }

    public function retries(): HasMany
    {
        return $this->hasMany(Execution::class, 'parent_execution_id');
    }

    public function waitingExecution(): HasOne
    {
        return $this->hasOne(WaitingExecution::class);
    }

    public function markAsRunning()
    {
        $this->update([
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    public function markAsSuccess($output = null)
    {
        $this->update([
            'status' => 'success',
            'output_data' => $output,
            'finished_at' => now(),
            'duration_ms' => $this->started_at 
                ? now()->diffInMilliseconds($this->started_at) 
                : null,
        ]);
    }

    public function markAsFailed($error)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
            'error_stack' => debug_backtrace(),
            'finished_at' => now(),
            'duration_ms' => $this->started_at 
                ? now()->diffInMilliseconds($this->started_at) 
                : null,
        ]);
    }

    public function canRetry(): bool
    {
        return $this->status === 'failed' && $this->retry_count < 3;
    }

    public function retry()
    {
        return Execution::create([
            'workflow_id' => $this->workflow_id,
            'mode' => 'retry',
            'input_data' => $this->input_data,
            'parent_execution_id' => $this->id,
            'retry_count' => $this->retry_count + 1,
        ]);
    }
}