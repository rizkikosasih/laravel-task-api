<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // reset cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        /*
        |-----------------------
        | PERMISSIONS
        |-----------------------
        */
        $permissions = [
            'create project',
            'update project',
            'delete project',

            'create task',
            'update task',
            'delete task',

            'create task comment',
            'delete task comment',
        ];

        $permissionModels = collect($permissions)->map(function ($permission) {
            return Permission::firstOrCreate(['name' => $permission]);
        });

        /*
        |-----------------------
        | ROLES
        |-----------------------
        */
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $member = Role::firstOrCreate(['name' => 'member']);

        /*
        |-----------------------
        | ASSIGN PERMISSIONS
        |-----------------------
        */

        // admin → semua permission
        $admin->syncPermissions($permissionModels);

        // member → limited access
        $member->syncPermissions([
            Permission::findByName('create task'),
            Permission::findByName('update task'),
            Permission::findByName('create task comment'),
            Permission::findByName('delete task comment'),
        ]);
    }
}
