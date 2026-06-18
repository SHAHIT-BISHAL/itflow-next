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
            'manage mail accounts',
            'manage pipelines',
            'view audit logs',
            'view clients',
            'manage clients',
            'view assets',
            'manage assets',
            'view documents',
            'manage documents',
            'view passwords',
            'manage passwords',
            'reveal passwords',
            'view domains',
            'manage domains',
            'view tickets',
            'manage tickets',
            'view deals',
            'manage deals',
            'view invoices',
            'manage invoices',
            'view payments',
            'manage payments',
            'view expenses',
            'manage expenses',
            'view reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        $technician = Role::firstOrCreate(['name' => 'Technician', 'guard_name' => 'web']);
        $technician->syncPermissions([
            'view clients',
            'manage clients',
            'view assets',
            'manage assets',
            'view documents',
            'manage documents',
            'view passwords',
            'manage passwords',
            'reveal passwords',
            'view domains',
            'manage domains',
            'view tickets',
            'manage tickets',
            'view reports',
        ]);

        $billing = Role::firstOrCreate(['name' => 'Billing', 'guard_name' => 'web']);
        $billing->syncPermissions([
            'view clients',
            'view invoices',
            'manage invoices',
            'view payments',
            'manage payments',
            'view expenses',
            'manage expenses',
            'view reports',
        ]);

        $sales = Role::firstOrCreate(['name' => 'Sales', 'guard_name' => 'web']);
        $sales->syncPermissions([
            'view clients',
            'manage clients',
            'view deals',
            'manage deals',
            'view invoices',
            'view reports',
        ]);

        $readOnly = Role::firstOrCreate(['name' => 'Read Only', 'guard_name' => 'web']);
        $readOnly->syncPermissions([
            'view clients',
            'view assets',
            'view documents',
            'view domains',
            'view tickets',
            'view deals',
            'view invoices',
            'view expenses',
            'view reports',
        ]);
    }
}
