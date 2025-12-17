<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->hasPermissionTo('order.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasPermissionTo('order.view')) {
            // Employees/Managers see all
            if ($user->hasRole(['admin', 'manager', 'employee'])) {
                return true;
            }
            // Clients see own
            return $user->id === $order->user_id;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->hasPermissionTo('order.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->hasPermissionTo('order.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->hasPermissionTo('order.delete');
    }
}
