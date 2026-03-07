<?php

namespace App\Policies;

use App\Models\Recepcion;
use App\Models\User;

class RecepcionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Recepcion $recepcion): bool
    {
        return (isAdmin() || $user->hasRole('almacen')) && $recepcion->is_sealed;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return isAdmin() || $user->hasRole('almacen');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Recepcion $recepcion): bool
    {
        return (isAdmin() || $user->hasRole('almacen')) && ! $recepcion->is_sealed && ! $recepcion->is_complete && ! $recepcion->deleted_at;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Recepcion $recepcion): bool
    {
        return (isAdmin() || $user->hasRole('almacen')) && ! $recepcion->is_sealed && ! $recepcion->is_complete;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Recepcion $recepcion): bool
    {
        return isAdmin() || $user->hasRole('almacen');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Recepcion $recepcion): bool
    {
        return isAdmin();
    }
}
