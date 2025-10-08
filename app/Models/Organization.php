<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
    ];

    /**
     * Get the owner of the organization.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The users that belong to the organization.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * The teams that belong to the organization.
     */
    public function teams()
    {
        return $this->hasMany(Team::class);
    }
}