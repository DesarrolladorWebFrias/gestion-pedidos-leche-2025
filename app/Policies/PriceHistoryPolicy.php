<?php

namespace App\Policies;

use App\Models\PriceHistory;
use App\Models\User;

class PriceHistoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'manager', 'employee']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PriceHistory $priceHistory): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'manager', 'employee']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'manager']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PriceHistory $priceHistory): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'manager']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PriceHistory $priceHistory): bool
    {
        return $user->hasRole(['super_admin', 'admin']);
    }
}
