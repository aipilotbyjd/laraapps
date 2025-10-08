<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'organization_id',
        'user_id',
    ];

    /**
     * Get the organization that the team belongs to.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the owner of the team.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class)->withPivot('role');
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }
}