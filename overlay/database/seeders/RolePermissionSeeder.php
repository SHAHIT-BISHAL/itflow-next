<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage users',
            'manage roles',
            'manage settings',
            'manage tags',
            'manage categories',
            'view clients',
            'manage clients',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        $technician = Role::firstOrCreate(['name' => 'Technician', 'guard_name' => 'web']);
        $technician->syncPermissions(['view clients', 'manage clients']);

        $readOnly = Role::firstOrCreate(['name' => 'Read Only', 'guard_name' => 'web']);
        $readOnly->syncPermissions(['view clients']);
    }
}
