<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tag extends Model
{
    protected $fillable = [
        'name', 'color', 'user_id'
    ];
    
    protected $casts = [
        'user_id' => 'integer',
    ];

    protected $attributes = [
        'color' => '#1f77b4',
    ];

    public function workflows(): BelongsToMany
    {
        return $this->belongsToMany(Workflow::class, 'workflow_tag');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}