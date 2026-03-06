<?php

namespace App\Traits;

use App\Models\Almacen;
use App\Models\Plan;
use App\Models\Stock;

trait CalcularStockTrait
{
    public ?Plan $plan;

    public ?Almacen $almacen;

    public string $codigoPlan = 'BM';

    public mixed $totalGeneral = null;

    public mixed $totalAsignacion = null;

    public mixed $totalPropia = null;

    public mixed $cantidadAsignacion = null;

    public mixed $cantidadPropia = null;

    public mixed $unidadesTotales = null;

    public bool $noExiste = false;

    public function calcularStock(): void
    {
        // 1. Buscamos el Plan por su código interno y el Almacén principal
        $this->plan = Plan::where('codigo', $this->codigoPlan)->first();
        $this->almacen = Almacen::where('is_main', 1)->first();

        // Si no existen, evitamos errores devolviendo un array vacío o stats por defecto
        if (! $this->plan || ! $this->almacen) {
            $this->noExiste = true;
        }

        // 2. Consulta de Stock optimizada para Almacén Principal + Plan Bm
        $query = Stock::where('almacenes_id', $this->almacen->id)
            ->where('planes_id', $this->plan->id);

        // 3. Cálculos de los totales
        $this->totalGeneral = $query->sum('total');
        $this->totalAsignacion = $query->sum('asignacion_total');
        $this->totalPropia = $query->sum('propia_total');
        // Calculamos el total de unidades físicas para el plan
        $this->cantidadAsignacion = $query->sum('asignacion_cantidad');
        $this->cantidadPropia = $query->sum('propia_cantidad');
        $this->unidadesTotales = $this->cantidadAsignacion + $this->cantidadPropia;
    }
}
