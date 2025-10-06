<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cron\CronExpression;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    protected $fillable = [
        'workflow_id', 'node_id', 'cron_expression',
        'timezone', 'active', 'last_run_at',
        'next_run_at', 'run_count'
    ];
    
    protected $casts = [
        'active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($schedule) {
            $schedule->calculateNextRun();
        });
        
        static::updating(function ($schedule) {
            if ($schedule->isDirty('cron_expression')) {
                $schedule->calculateNextRun();
            }
        });
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function calculateNextRun()
    {
        $cron = new CronExpression($this->cron_expression);
        $this->next_run_at = $cron->getNextRunDate('now', 0, false, $this->timezone);
    }

    public function isDue(): bool
    {
        return $this->active && $this->next_run_at <= now();
    }

    public function markAsRun()
    {
        $this->increment('run_count');
        $this->update(['last_run_at' => now()]);
        $this->calculateNextRun();
        $this->save();
    }
}