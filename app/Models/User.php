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

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'subscription_level',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Define subscription levels
     */
    const SUBSCRIPTION_LEVELS = [
        'free' => [
            'max_workflows' => 5,
            'max_executions_per_month' => 100,
            'advanced_features' => false,
            'priority_support' => false,
            'max_team_members' => 5,
        ],
        'pro' => [
            'max_workflows' => 50,
            'max_executions_per_month' => 1000,
            'advanced_features' => true,
            'priority_support' => true,
            'max_team_members' => 25,
        ],
        'enterprise' => [
            'max_workflows' => 999999, // Unlimited
            'max_executions_per_month' => 999999, // Unlimited
            'advanced_features' => true,
            'priority_support' => true,
            'max_team_members' => 999999, // Unlimited
        ],
    ];

    /**
     * Get user's subscription details
     */
    public function getSubscriptionDetails()
    {
        $level = $this->subscription_level ?? 'free';
        return self::SUBSCRIPTION_LEVELS[$level] ?? self::SUBSCRIPTION_LEVELS['free'];
    }

    /**
     * Check if user can create more workflows
     */
    public function canCreateMoreWorkflows()
    {
        $subscription = $this->getSubscriptionDetails();
        $currentWorkflows = $this->workflows()->count();
        
        return $currentWorkflows < $subscription['max_workflows'];
    }

    /**
     * Check if user has a specific permission
     */
    public function hasWorkflowPermission($permission)
    {
        // Check direct permissions first
        if ($this->hasPermissionTo($permission)) {
            return true;
        }
        
        // Check role-based permissions
        return $this->hasPermissionTo($permission);
    }

    public function canAddMoreTeamMembers(Organization $organization): bool
    {
        $subscription = $organization->owner->getSubscriptionDetails();
        $currentMembers = $organization->users()->count();

        return $currentMembers < $subscription['max_team_members'];
    }

    /**
     * User workflows relationship
     */
    public function workflows()
    {
        return $this->hasMany(Workflow::class);
    }

    /**
     * The organizations that the user belongs to.
     */
    public function organizations()
    {
        return $this->belongsToMany(Organization::class);
    }

    /**
     * The organizations that the user owns.
     */
    public function ownedOrganizations()
    {
        return $this->hasMany(Organization::class);
    }

    /**
     * The teams that the user owns.
     */
    public function ownedTeams()
    {
        return $this->hasMany(Team::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class);
    }

    public function currentTeam()
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    public function belongsToTeam(Team $team): bool
    {
        return $this->teams->contains($team);
    }

    public function belongsToOrganization(Organization $organization): bool
    {
        return $this->organizations->contains($organization);
    }

    public function isOwnerOfOrganization(Organization $organization): bool
    {
        return $this->id === $organization->user_id;
    }

    public function isOwnerOfTeam(Team $team): bool
    {
        return $this->id === $team->user_id;
    }

    public function hasTeamPermission(Organization $organization, string $permission): bool
    {
        foreach ($organization->teams as $team) {
            if ($this->hasPermissionTo($permission, $team->id)) {
                return true;
            }
        }

        return false;
    }
}
