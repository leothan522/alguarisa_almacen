<?php

namespace App\Observers;

use App\Models\Recepcion;

class RecepcionObserver
{
    /**
     * Handle the Recepcion "created" event.
     */
    public function created(Recepcion $recepcion): void
    {
        //
    }

    /**
     * Handle the Recepcion "updated" event.
     */
    public function updated(Recepcion $recepcion): void
    {
        //
    }

    /**
     * Handle the Recepcion "deleted" event.
     */
    public function deleted(Recepcion $recepcion): void
    {
        // Forzamos el recálculo. Como el registro ya tiene deleted_at (o ya no existe),
        // el método sincronizarStock() en el modelo lo ignorará en la suma.
        $recepcion->sincronizarStock();
    }

    /**
     * Handle the Recepcion "restored" event.
     */
    public function restored(Recepcion $recepcion): void
    {
        // Forzamos el recálculo. Como el registro ya tiene deleted_at (o ya no existe),
        // el método sincronizarStock() en el modelo lo ignorará en la suma.
        $recepcion->sincronizarStock();
    }

    /**
     * Handle the Recepcion "force deleted" event.
     */
    public function forceDeleted(Recepcion $recepcion): void
    {
        //
    }
}
