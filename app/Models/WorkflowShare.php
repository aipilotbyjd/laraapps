<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowShare extends Model
{
    protected $fillable = [
        'workflow_id', 'user_id', 'permission'
    ];
    
    protected $casts = [
        'user_id' => 'integer',
        'workflow_id' => 'integer',
    ];

    protected $attributes = [
        'permission' => 'view',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}