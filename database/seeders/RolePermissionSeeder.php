<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Schema::disableForeignKeyConstraints();

        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('permissions')->truncate();

        Schema::enableForeignKeyConstraints();

        $admin = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
        $member = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'member']);

        $permissions = [
            'view project',
            'create project',
            'update project',
            'delete project',

            'view task',
            'create task',
            'update task detail',
            'update task status',
            'delete task',

            'view comment',
            'create comment',
            'delete comment',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $admin->syncPermissions(Permission::all());
        $member->syncPermissions([
            'view project',
            'view task',
            'view comment',
            'update task status',
            'create comment',
            'delete comment',
        ]);
    }
}
