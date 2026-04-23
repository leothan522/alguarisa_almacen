<?php

namespace App\Policies;

use App\Models\Responsable;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ResponsablePolicy
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
    public function view(User $user, Responsable $responsable): bool
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
    public function update(User $user, Responsable $responsable): bool
    {
        return isAdmin() || $user->hasRole('almacen');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Responsable $responsable): bool
    {
        $hasRelations = $responsable->recepciones()->exists() || $responsable->despachos()->exists();

        return (isAdmin() || $user->hasRole('almacen')) && ! $hasRelations;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Responsable $responsable): bool
    {
        return isAdmin() || $user->hasRole('almacen');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Responsable $responsable): bool
    {
        $hasRelations = $responsable->recepciones()->exists() || $responsable->despachos()->exists();

        return isAdmin() && ! $hasRelations;
    }
}
