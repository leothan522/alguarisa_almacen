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

    public mixed $bolsas = null;

    public function calcularStock(): void
    {
        /*// 1. Buscamos el Plan por su código interno y el Almacén principal
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
        $this->unidadesTotales = $query->sum('stock_cantidad');
        $this->totalGeneral = $query->sum('stock_total');
        $this->totalAsignacion = $query->sum('asignacion_total') - $query->sum('despacho_asignacion_total');
        $this->totalPropia = $query->sum('propia_total') - $query->sum('despacho_propia_total');
        // Calculamos el total de unidades físicas para el plan
        $this->cantidadAsignacion = $query->sum('asignacion_cantidad') - $query->sum('despacho_asignacion_cantidad');
        $this->cantidadPropia = $query->sum('propia_cantidad') - $query->sum('despacho_propia_cantidad');

        if ($this->codigoPlan = 'MC') {
            $bolsas = $query->where('rubros_id', 3)->first();
            if ($bolsas) {
                $this->bolsas = $bolsas->stock_cantidad;
            }
        }*/

        // 1. Buscamos el Plan por su código interno y el Almacén principal
        $this->plan = Plan::where('codigo', $this->codigoPlan)->first();
        $this->almacen = Almacen::where('is_main', 1)->first();

        // Si alguno no existe, marcamos la bandera y detenemos la ejecución de forma segura
        if (! $this->plan || ! $this->almacen) {
            $this->noExiste = true;

            return;
        }

        $this->noExiste = false;

        // 2. Base de la consulta de Stock para Almacén Principal + Plan seleccionado
        $query = Stock::where('almacenes_id', $this->almacen->id)
            ->where('planes_id', $this->plan->id);

        // 3. Cálculos de totales generales (Unidades y Pesos)
        $this->unidadesTotales = (clone $query)->sum('stock_cantidad');
        $this->totalGeneral = (clone $query)->sum('stock_total');

        // Totales desglosados (Pesos netos)
        $this->totalAsignacion = (clone $query)->sum('asignacion_total') - (clone $query)->sum('despacho_asignacion_total');
        $this->totalPropia = (clone $query)->sum('propia_total') - (clone $query)->sum('despacho_propia_total');

        // Totales desglosados (Unidades físicas)
        $this->cantidadAsignacion = (clone $query)->sum('asignacion_cantidad') - (clone $query)->sum('despacho_asignacion_cantidad');
        $this->cantidadPropia = (clone $query)->sum('propia_cantidad') - (clone $query)->sum('despacho_propia_cantidad');

        // 4. Lógica específica para Plan Módulos / combos ('MC')
        if ($this->codigoPlan === 'MC') {
            $bolsas = (clone $query)->where('rubros_id', 3)->first();
            if ($bolsas) {
                $this->bolsas = $bolsas->stock_cantidad;
            }
        }

    }
}
