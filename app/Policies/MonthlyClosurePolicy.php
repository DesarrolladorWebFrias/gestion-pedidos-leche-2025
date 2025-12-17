<?php

namespace App\Policies;

use App\Models\MonthlyClosure;
use App\Models\User;

class MonthlyClosurePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->hasPermissionTo('monthly_closure.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MonthlyClosure $monthlyClosure): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->hasPermissionTo('monthly_closure.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->hasPermissionTo('monthly_closure.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MonthlyClosure $monthlyClosure): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->hasPermissionTo('monthly_closure.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MonthlyClosure $monthlyClosure): bool
    {
        return $user->hasRole('super_admin');
    }
}
