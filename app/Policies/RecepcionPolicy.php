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
        return ! $user->hasRole('Bodega Movil');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Recepcion $recepcion): bool
    {
        return true;
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
        $edit = ! $recepcion->is_sealed && ! $recepcion->is_complete && ! $recepcion->deleted_at;

        return (isAdmin() || $user->hasRole('almacen')) && $edit;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Recepcion $recepcion): bool
    {
        $delete = ! $recepcion->is_sealed && ! $recepcion->is_complete;

        return (isAdmin() || $user->hasRole('almacen')) && $delete;
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
        $delete = ! $recepcion->is_sealed && ! $recepcion->is_complete;

        return isAdmin() && $delete;
    }
}
