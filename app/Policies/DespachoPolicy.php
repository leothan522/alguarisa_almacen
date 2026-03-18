<?php

namespace App\Policies;

use App\Models\Despacho;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DespachoPolicy
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
    public function view(User $user, Despacho $despacho): bool
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
    public function update(User $user, Despacho $despacho): bool
    {
        $edit = ! $despacho->is_merma && ! $despacho->is_return && ! $despacho->is_complete && ! $despacho->deleted_at;

        return (isAdmin() || $user->hasRole('almacen')) && $edit;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Despacho $despacho): bool
    {
        $delete = ! $despacho->is_return && ! $despacho->is_complete;

        return (isAdmin() || $user->hasRole('almacen')) && $delete;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Despacho $despacho): bool
    {
        return isAdmin() || $user->hasRole('almacen');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Despacho $despacho): bool
    {
        $delete = ! $despacho->is_return && ! $despacho->is_complete;

        return isAdmin() && $delete;
    }
}
