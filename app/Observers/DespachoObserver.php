<?php

namespace App\Observers;

use App\Models\Despacho;

class DespachoObserver
{
    /**
     * Handle the Despacho "created" event.
     */
    public function created(Despacho $despacho): void
    {
        //
    }

    /**
     * Handle the Despacho "updated" event.
     */
    public function updated(Despacho $despacho): void
    {
        //
    }

    /**
     * Handle the Despacho "deleted" event.
     */
    public function deleted(Despacho $despacho): void
    {
        // Forzamos el recálculo. Como el registro ya tiene deleted_at (o ya no existe),
        // el método sincronizarStock() en el modelo lo ignorará en la suma.
        $despacho->sincronizarStock();
    }

    /**
     * Handle the Despacho "restored" event.
     */
    public function restored(Despacho $despacho): void
    {
        // Forzamos el recálculo. Como el registro ya tiene deleted_at (o ya no existe),
        // el método sincronizarStock() en el modelo lo ignorará en la suma.
        $despacho->sincronizarStock();
    }

    /**
     * Handle the Despacho "force deleted" event.
     */
    public function forceDeleted(Despacho $despacho): void
    {
        //
    }
}
