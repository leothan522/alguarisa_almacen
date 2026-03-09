<?php

namespace App\Policies;

use App\Models\Rubro;
use App\Models\User;

class RubroPolicy
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
    public function view(User $user, Rubro $rubro): bool
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
    public function update(User $user, Rubro $rubro): bool
    {
        return isAdmin() || $user->hasRole('almacen');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Rubro $rubro): bool
    {
        $hasRelations = $rubro->items()->exists() || $rubro->stocks()->exists();

        return (isAdmin() || $user->hasRole('almacen')) && ! $hasRelations;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Rubro $rubro): bool
    {
        return isAdmin() || $user->hasRole('almacen');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Rubro $rubro): bool
    {
        $hasRelations = $rubro->items()->exists() || $rubro->stocks()->exists();

        return isAdmin() && ! $hasRelations;
    }
}
