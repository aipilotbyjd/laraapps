<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Webhook extends Model
{
    protected $fillable = [
        'workflow_id', 'node_id', 'webhook_id', 'path',
        'method', 'active', 'response_mode', 
        'request_count', 'last_called_at'
    ];
    
    protected $casts = [
        'active' => 'boolean',
        'response_mode' => 'array',
        'last_called_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($webhook) {
            if (!$webhook->webhook_id) {
                $webhook->webhook_id = Str::uuid();
            }
            if (!$webhook->path) {
                $webhook->path = '/webhook/' . $webhook->webhook_id;
            }
        });
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function incrementRequestCount()
    {
        $this->increment('request_count');
        $this->update(['last_called_at' => now()]);
    }

    public function getFullUrlAttribute()
    {
        return url($this->path);
    }
}