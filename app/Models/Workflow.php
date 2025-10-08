<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Workflow extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'name', 'description', 'active', 'nodes', 
        'connections', 'settings', 'static_data', 
        'user_id', 'folder_id'
    ];
    
    protected $casts = [
        'active' => 'boolean',
        'nodes' => 'array',
        'connections' => 'array',
        'settings' => 'array',
        'static_data' => 'array',
        'last_executed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::updated(function ($workflow) {
            if ($workflow->isDirty(['nodes', 'connections'])) {
                $workflow->createVersion();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function executions(): HasMany
    {
        return $this->hasMany(Execution::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(WorkflowVersion::class);
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'workflow_tag');
    }

    public function shares(): HasMany
    {
        return $this->hasMany(WorkflowShare::class);
    }

    public function createVersion()
    {
        $latestVersion = $this->versions()->max('version_number') ?? 0;
        
        return $this->versions()->create([
            'version_number' => $latestVersion + 1,
            'nodes' => $this->nodes,
            'connections' => $this->connections,
            'settings' => $this->settings,
            'created_by' => auth()->user()?->name,
        ]);
    }

    public function getTriggerNodes()
    {
        return collect($this->nodes)->filter(function ($node) {
            return str_contains($node['type'], 'trigger');
        });
    }

    public function getNodeById($nodeId)
    {
        return collect($this->nodes)->firstWhere('id', $nodeId);
    }

    public function incrementExecutionCount()
    {
        $this->increment('execution_count');
        $this->update(['last_executed_at' => now()]);
    }
}