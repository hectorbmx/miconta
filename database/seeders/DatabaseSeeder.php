<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Ejecutamos primero el Seeder de Roles para que existan en la DB
        $this->call(RoleSeeder::class);

        // 2. Creamos tu usuario Super Admin
        $admin = User::create([
            'name' => 'Admin SaaS',
            'email' => 'admin@sat.com',
            'password' => Hash::make('12345678'), // Cambia esto
            // 'role' => 'admin', // Mantenemos el campo por si acaso, pero...
            'tenant_id' => null, // El admin global no pertenece a un despacho
        ]);

        // 3. Le asignamos el rol de Spatie
        $admin->assignRole('admin');

        // Opcional: Crear un contador de prueba para testear el flujo del SaaS
        /*
        $tenant = \App\Models\Tenant::create(['name' => 'Despacho Contable S.C.']);
        
        $contador = User::create([
            'name' => 'Contador de Prueba',
            'email' => 'contador@test.com',
            'password' => Hash::make('password'),
            'role' => 'tenant',
            'tenant_id' => $tenant->id,
        ]);
        $contador->assignRole('contador');
        */
    }
}