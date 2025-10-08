<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Reset cached roles and permissions
            app()['cache']->forget('spatie.permission.cache');

            // create permissions
            $permissions = [
                'workflows.create', 'workflows.read', 'workflows.update', 'workflows.delete',
                'credentials.create', 'credentials.read', 'credentials.update', 'credentials.delete',
                'team.manage',
            ];

            foreach ($permissions as $permission) {
                Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
            }

            // create roles and assign created permissions
            $ownerRole = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'api']);
            $ownerRole->givePermissionTo(Permission::where('guard_name', 'api')->get());

            $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
            $adminRole->givePermissionTo([
                'workflows.create',
                'workflows.read',
                'workflows.update',
                'workflows.delete',
                'credentials.create',
                'credentials.read',
                'credentials.update',
                'credentials.delete',
                'team.manage',
            ]);

            $editorRole = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'api']);
            $editorRole->givePermissionTo([
                'workflows.create',
                'workflows.read',
                'workflows.update',
                'credentials.create',
                'credentials.read',
                'credentials.update',
            ]);

            $viewerRole = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'api']);
            $viewerRole->givePermissionTo([
                'workflows.read',
            ]);
        });
    }
}
