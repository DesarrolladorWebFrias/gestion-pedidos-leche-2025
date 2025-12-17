<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Roles
        $roles = [
            'super_admin' => 'Administrador con acceso completo al sistema',
            'admin' => 'Administrador del sistema',
            'manager' => 'Gerente con permisos de supervisión',
            'employee' => 'Empleado estándar',
            'client' => 'Cliente del sistema',
        ];

        foreach ($roles as $name => $desc) {
            Role::firstOrCreate(['name' => $name], ['description' => $desc, 'guard_name' => 'web']);
        }

        // 2. Permissions
        $permissions = [
            ['name' => 'user.view', 'group' => 'Usuarios', 'description' => 'Ver usuarios'],
            ['name' => 'user.create', 'group' => 'Usuarios', 'description' => 'Crear usuarios'],
            ['name' => 'user.edit', 'group' => 'Usuarios', 'description' => 'Editar usuarios'],
            ['name' => 'user.delete', 'group' => 'Usuarios', 'description' => 'Eliminar usuarios'],

            ['name' => 'product.view', 'group' => 'Productos', 'description' => 'Ver productos'],
            ['name' => 'product.create', 'group' => 'Productos', 'description' => 'Crear productos'],
            ['name' => 'product.edit', 'group' => 'Productos', 'description' => 'Editar productos'],
            ['name' => 'product.delete', 'group' => 'Productos', 'description' => 'Eliminar productos'],

            ['name' => 'order.view', 'group' => 'Pedidos', 'description' => 'Ver pedidos'],
            ['name' => 'order.create', 'group' => 'Pedidos', 'description' => 'Crear pedidos'],
            ['name' => 'order.edit', 'group' => 'Pedidos', 'description' => 'Editar pedidos'],
            ['name' => 'order.delete', 'group' => 'Pedidos', 'description' => 'Eliminar pedidos'],
            ['name' => 'order.confirm', 'group' => 'Pedidos', 'description' => 'Confirmar pedidos'],
            ['name' => 'order.deliver', 'group' => 'Pedidos', 'description' => 'Entregar pedidos'],

            ['name' => 'payment.view', 'group' => 'Pagos', 'description' => 'Ver pagos'],
            ['name' => 'payment.create', 'group' => 'Pagos', 'description' => 'Crear pagos'],
            ['name' => 'payment.edit', 'group' => 'Pagos', 'description' => 'Editar pagos'],
            ['name' => 'payment.delete', 'group' => 'Pagos', 'description' => 'Eliminar pagos'],

            ['name' => 'monthly_closure.view', 'group' => 'Cierres', 'description' => 'Ver cierres mensuales'],
            ['name' => 'monthly_closure.create', 'group' => 'Cierres', 'description' => 'Crear cierres mensuales'],
            ['name' => 'monthly_closure.edit', 'group' => 'Cierres', 'description' => 'Editar cierres mensuales'],
            ['name' => 'monthly_closure.close', 'group' => 'Cierres', 'description' => 'Cerrar periodos mensuales'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm['name']], [
                'group' => $perm['group'],
                'description' => $perm['description'],
                'guard_name' => 'web'
            ]);
        }

        // 3. Asignar permisos a Roles (Simplificado)
        $superAdmin = Role::where('name', 'super_admin')->first();
        $superAdmin->givePermissionTo(Permission::all());

        // 4. Asegurar usuario admin
        $adminUser = User::where('email', 'admin@lechera.com')->first();
        if ($adminUser) {
            $adminUser->assignRole($superAdmin);
        }
    }
}
