<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SyncUserTypeToRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $roleToAssign = null;

            // 1. Caso Especial: Super Admin
            if ($user->email === 'admin@lechera.com') {
                $roleToAssign = 'super_admin';
            } 
            // 2. Mapeo por User Type
            else {
                switch ($user->user_type) {
                    case 'admin':
                        $roleToAssign = 'admin';
                        break;
                    case 'empleado':
                        $roleToAssign = 'employee';
                        break;
                    case 'cliente':
                        $roleToAssign = 'client';
                        break;
                }
            }

            if ($roleToAssign) {
                // Verificar si ya tiene el rol para no duplicar o borrar otros
                if (!$user->hasRole($roleToAssign)) {
                    $user->assignRole($roleToAssign);
                    $this->command->info("Usuario {$user->email} ({$user->user_type}) -> Asignado rol: {$roleToAssign}");
                }
            } else {
                $this->command->warn("Usuario {$user->email} no coincide con ningún tipo conocido.");
            }
        }
    }
}
