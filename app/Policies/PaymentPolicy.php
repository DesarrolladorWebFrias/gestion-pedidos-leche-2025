<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->hasPermissionTo('payment.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Payment $payment): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasPermissionTo('payment.view')) {
            if ($user->hasRole(['admin', 'manager', 'employee'])) {
                return true;
            }
            return $payment->order && $user->id === $payment->order->user_id; 
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
        return $user->hasPermissionTo('payment.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Payment $payment): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->hasPermissionTo('payment.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Payment $payment): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->hasPermissionTo('payment.delete');
    }
}
