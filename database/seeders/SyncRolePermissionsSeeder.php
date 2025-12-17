<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class SyncRolePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Definir la matriz de Permisos por Rol
        
        // --- CLIENTE ---
        $clientPermissions = [
            'product.view',     // Ver catálogo
            'order.view',       // Ver sus pedidos
            'order.create',     // Crear pedidos
            'payment.view',     // Ver historial de pagos
            'payment.create',   // Registrar pagos
            // Generalmente no editan ni borran tras enviar
        ];

        // --- EMPLEADO ---
        $employeePermissions = [
            'user.view',        // Ver lista de clientes
            
            'product.view',     // Ver inventario
            // 'product.edit',  // Podría habilitarse si gestionan stock
            
            'order.view',
            'order.create',     // Tomar pedidos por teléfono
            'order.edit',       // Actualizar estado
            'order.confirm',    // Confirmar
            'order.deliver',    // Entregar
            
            'payment.view',
            'payment.create',
            'payment.edit',     // Validar pagos (cambiar estado a aprobado)
            
            'price_history.view', // Ver historial de precios

            // Permisos de Cierre Mensual (Solicitado explícitamente)
            'monthly_closure.view',
            'monthly_closure.create', // Habilitar nuevos cierres
            'monthly_closure.edit',   // Cerrar mes y poner en inventario
        ];

        // --- MANAGER ---
        $managerPermissions = [
            'user.view', 'user.create', 'user.edit', // Gestión completa de clientes
            'product.view', 'product.create', 'product.edit', // Gestión producto
            'order.view', 'order.create', 'order.edit', 'order.delete', 'order.confirm', 'order.deliver',
            'payment.view', 'payment.create', 'payment.edit',
            'monthly_closure.view', // Ver reportes
            'price_history.view', 'price_history.create'
        ];

        // --- ADMIN ---
        // (El Admin suele tener todo excepto quizás borrar configuraciones críticas, 
        // pero aquí le daremos casi todo lo operativo)
        $adminPermissions = Permission::all()->pluck('name')->toArray();


        // 2. Aplicar Asignaciones
        // Usamos syncPermissions para asegurar que tengan EXACTAMENTE estos permisos y limpiar antiguos
        
        $this->syncRole('client', $clientPermissions);
        $this->syncRole('employee', $employeePermissions);
        $this->syncRole('manager', $managerPermissions);
        
        // Admin: Asignamos todo lo existente
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->syncPermissions($adminPermissions);
        }
        
    }

    private function syncRole(string $roleName, array $permissions): void
    {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            // Validar que los permisos existan antes de asignar
            $validPermissions = Permission::whereIn('name', $permissions)->get();
            $role->syncPermissions($validPermissions);
            $this->command->info("Rol '{$roleName}' sincronizado con " . $validPermissions->count() . " permisos.");
        } else {
            $this->command->warn("Rol '{$roleName}' no encontrado.");
        }
    }
}
