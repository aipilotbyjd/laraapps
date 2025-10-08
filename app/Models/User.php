<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'subscription_level',
        'current_team_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class);
    }

    public function ownedOrganizations()
    {
        return $this->hasMany(Organization::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class)->withPivot('role');
    }

    public function currentTeam()
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    public function belongsToTeam(Team $team): bool
    {
        return $this->teams()->where('team_id', $team->id)->exists();
    }

    public function isOwnerOfOrganization(Organization $organization): bool
    {
        return $this->id === $organization->user_id;
    }
}