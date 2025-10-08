<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // Workflow permissions
            'workflow.create',
            'workflow.view',
            'workflow.update',
            'workflow.delete',
            'workflow.execute',
            'workflow.duplicate',
            'workflow.activate',
            'workflow.deactivate',
            
            // Execution permissions
            'execution.view',
            'execution.retry',
            'execution.cancel',
            'execution.delete',
            
            // Credential permissions
            'credential.create',
            'credential.view',
            'credential.update',
            'credential.delete',
            'credential.test',
            
            // Tag permissions
            'tag.create',
            'tag.view',
            'tag.update',
            'tag.delete',
            
            // Schedule permissions
            'schedule.create',
            'schedule.view',
            'schedule.update',
            'schedule.delete',
            
            // Webhook permissions
            'webhook.create',
            'webhook.view',
            'webhook.update',
            'webhook.delete',
            'webhook.test',
            
            // Advanced features (for paid subscriptions)
            'feature.advanced-nodes',
            'feature.custom-code',
            'feature.priority-support',
            'feature.unlimited-executions',
            'feature.team-collaboration',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $roles = [
            'free-user' => [
                'workflow.create',
                'workflow.view',
                'workflow.update',
                'workflow.delete',
                'workflow.execute',
                'workflow.duplicate',
                'workflow.activate',
                'workflow.deactivate',
                'execution.view',
                'execution.retry',
                'execution.cancel',
                'credential.create',
                'credential.view',
                'credential.update',
                'credential.delete',
                'credential.test',
                'tag.create',
                'tag.view',
                'tag.update',
                'tag.delete',
                'schedule.create',
                'schedule.view',
                'schedule.update',
                'schedule.delete',
                'webhook.create',
                'webhook.view',
                'webhook.update',
                'webhook.delete',
                'webhook.test',
            ],
            'pro-user' => [
                'workflow.create',
                'workflow.view',
                'workflow.update',
                'workflow.delete',
                'workflow.execute',
                'workflow.duplicate',
                'workflow.activate',
                'workflow.deactivate',
                'execution.view',
                'execution.retry',
                'execution.cancel',
                'execution.delete',
                'credential.create',
                'credential.view',
                'credential.update',
                'credential.delete',
                'credential.test',
                'tag.create',
                'tag.view',
                'tag.update',
                'tag.delete',
                'schedule.create',
                'schedule.view',
                'schedule.update',
                'schedule.delete',
                'webhook.create',
                'webhook.view',
                'webhook.update',
                'webhook.delete',
                'webhook.test',
                'feature.advanced-nodes',
                'feature.priority-support',
            ],
            'enterprise-user' => [
                'workflow.create',
                'workflow.view',
                'workflow.update',
                'workflow.delete',
                'workflow.execute',
                'workflow.duplicate',
                'workflow.activate',
                'workflow.deactivate',
                'execution.view',
                'execution.retry',
                'execution.cancel',
                'execution.delete',
                'credential.create',
                'credential.view',
                'credential.update',
                'credential.delete',
                'credential.test',
                'tag.create',
                'tag.view',
                'tag.update',
                'tag.delete',
                'schedule.create',
                'schedule.view',
                'schedule.update',
                'schedule.delete',
                'webhook.create',
                'webhook.view',
                'webhook.update',
                'webhook.delete',
                'webhook.test',
                'feature.advanced-nodes',
                'feature.custom-code',
                'feature.priority-support',
                'feature.unlimited-executions',
                'feature.team-collaboration',
            ],
            'admin' => $permissions, // Admin gets all permissions
        ];

        // Create roles and assign permissions
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            
            // Sync permissions to avoid duplicates
            $permissionsToAssign = [];
            foreach ($rolePermissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission) {
                    $permissionsToAssign[] = $permission;
                }
            }
            $role->syncPermissions($permissionsToAssign);
        }

        // Assign default role to existing users
        $users = User::all();
        foreach ($users as $user) {
            // Skip if user already has a role
            if ($user->roles()->count() > 0) {
                continue;
            }
            
            $subscriptionLevel = $user->subscription_level ?? 'free';
            
            switch ($subscriptionLevel) {
                case 'pro':
                    $user->assignRole('pro-user');
                    break;
                case 'enterprise':
                    $user->assignRole('enterprise-user');
                    break;
                default:
                    $user->assignRole('free-user');
                    break;
            }
        }
    }
}
