<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Despacho extends Model
{
    use SoftDeletes;

    protected $table = 'despachos';

    protected $fillable = [
        'numero',
        'fecha',
        'hora',
        'observacion',
        'almacenes_id',
        'planes_id',
        'jefes_id',
        'jefes_nombre',
        'jefes_cedula',
        'responsables_id',
        'responsables_nombre',
        'responsables_cedula',
        'responsables_telefono',
        'responsables_empresa',
        'is_return',
        'is_complete',
        'pdf_expediente',
    ];

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacenes_id', 'id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'planes_id', 'id');
    }

    public function jefe(): BelongsTo
    {
        return $this->belongsTo(Jefe::class, 'jefes_id', 'id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Responsable::class, 'responsables_id', 'id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(Detalle::class, 'despachos_id', 'id');
    }

    public function sincronizarStock(): void
    {
        // 1. Obtenemos los rubros involucrados en este despacho
        $rubrosIds = \DB::table('despachos_detalles')
            ->where('despachos_id', $this->id)
            ->pluck('rubros_id')
            ->unique();

        foreach ($rubrosIds as $rubroId) {
            // 2. Buscamos el registro de Stock
            $stock = Stock::firstOrCreate([
                'planes_id' => $this->planes_id,
                'rubros_id' => $rubroId,
                'almacenes_id' => $this->almacenes_id,
            ]);

            // 3. Calculamos totales de despachos (Salidas)
            $totalesDespacho = \DB::table('despachos_detalles')
                ->join('despachos', 'despachos_detalles.despachos_id', '=', 'despachos.id')
                ->where('despachos.planes_id', $this->planes_id)
                ->where('despachos_detalles.rubros_id', $rubroId)
                ->whereNull('despachos.deleted_at')
                ->selectRaw("
                SUM(CASE WHEN tipo_adquisicion = 'asignacion' THEN cantidad_unidades ELSE 0 END) as asig_cant,
                SUM(CASE WHEN tipo_adquisicion = 'asignacion' THEN (cantidad_unidades * peso_unitario) ELSE 0 END) as asig_peso,
                SUM(CASE WHEN tipo_adquisicion = 'propia' THEN cantidad_unidades ELSE 0 END) as prop_cant,
                SUM(CASE WHEN tipo_adquisicion = 'propia' THEN (cantidad_unidades * peso_unitario) ELSE 0 END) as prop_peso
            ")
                ->first();

            $despachoPesoTotal = ($totalesDespacho->asig_peso ?? 0) + ($totalesDespacho->prop_peso ?? 0);

            // 4. Actualizamos el registro de Stock
            $stock->update([
                'despacho_asignacion_cantidad' => $totalesDespacho->asig_cant ?? 0,
                'despacho_asignacion_total' => $totalesDespacho->asig_peso ?? 0,
                'despacho_propia_cantidad' => $totalesDespacho->prop_cant ?? 0,
                'despacho_propia_total' => $totalesDespacho->prop_peso ?? 0,
                'despacho_total' => $despachoPesoTotal,

                // 'total' se mantiene como la suma de recepciones (Entradas)
                // 'stock_total' es el balance neto actual
                'stock_cantidad' => ($stock->asignacion_cantidad + $stock->propia_cantidad) - ($totalesDespacho->asig_cant + $totalesDespacho->prop_cant),
                'stock_total' => $stock->total - $despachoPesoTotal,
            ]);
        }
    }
}
