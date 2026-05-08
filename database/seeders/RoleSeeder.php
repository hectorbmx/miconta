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
    $admin = Role::create(['name' => 'admin']);
    $contador = Role::create(['name' => 'contador']);

    // Permisos de ejemplo
    Permission::create(['name' => 'manage tenants'])->assignRole($admin);
    Permission::create(['name' => 'manage taxpayers'])->assignRole($contador);
    Permission::create(['name' => 'download cfdi'])->assignRole($contador);
}
}
