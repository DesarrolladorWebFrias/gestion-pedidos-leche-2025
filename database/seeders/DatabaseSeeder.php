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
        $this->call(InitialDataSeeder::class);

        // Crear Admin si no existe (ya sea por InitialDataSeeder o aquí)
        // InitialDataSeeder asigna rol, pero necesitamos el usuario primero.
        // Vamos a modificar esto para que sea seguro

        $admin = User::firstOrCreate(
            ['email' => 'admin@lechera.com'],
            [
                'name' => 'Administrador Principal',
                'password' => Hash::make('12345678'), // Password por defecto
                'user_type' => 'admin',
                'status' => 'activo',
                'registered_at' => now(),
            ]
        );

        // Asignar rol super admin
        $admin->assignRole('super_admin');
    }
}
