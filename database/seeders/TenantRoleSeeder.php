<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class TenantRoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'tenant.view_dashboard',
            'tenant.manage_users',
            'tenant.manage_customers',
            'tenant.manage_customer_plans',
            'tenant.manage_sat',
            'tenant.manage_accounting',
            'tenant.manage_billing',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $roles = [
            'tenant_admin' => $permissions,
            'tenant_contador' => [
                'tenant.view_dashboard',
                'tenant.manage_customers',
                'tenant.manage_sat',
                'tenant.manage_accounting',
            ],
            'tenant_auxiliar' => [
                'tenant.view_dashboard',
                'tenant.manage_customers',
                'tenant.manage_sat',
            ],
            'tenant_lectura' => [
                'tenant.view_dashboard',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($rolePermissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
