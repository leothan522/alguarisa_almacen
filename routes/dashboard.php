<?php

use App\Http\Controllers\Dashboard\DespachoController;
use App\Http\Controllers\Dashboard\RecepcionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('export-pdf/{id}/recepcion', [RecepcionController::class, 'descargarRecepcion'])->name('export-pdf.recepcion');
    Route::get('export-pdf/{id}/despacho', [DespachoController::class, 'descargarDespacho'])->name('export-pdf.despacho');

});
