<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
  public function run(): void
{
    // Roles principales
    $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $contador = Role::firstOrCreate(['name' => 'contador', 'guard_name' => 'web']);

    // Permisos de ejemplo
    Permission::firstOrCreate(['name' => 'manage tenants', 'guard_name' => 'web'])->assignRole($admin);
    Permission::firstOrCreate(['name' => 'manage taxpayers', 'guard_name' => 'web'])->assignRole($contador);
    Permission::firstOrCreate(['name' => 'download cfdi', 'guard_name' => 'web'])->assignRole($contador);

    $this->call(TenantRoleSeeder::class);
}
}
