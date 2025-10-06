<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowVersion extends Model
{
    protected $fillable = [
        'workflow_id', 'version_number', 'nodes',
        'connections', 'settings', 'created_by', 'change_notes'
    ];
    
    protected $casts = [
        'nodes' => 'array',
        'connections' => 'array',
        'settings' => 'array',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }
}