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
        'is_merma',
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

    public function sincronizarStock(array $idsAdicionales = []): void
    {
        // 1. Obtenemos los rubros que actualmente están en el despacho
        $idsActuales = \DB::table('despachos_detalles')
            ->where('despachos_id', $this->id)
            ->pluck('rubros_id')
            ->toArray();

        // 2. Unimos con los IDs que pasamos manualmente (los que se borraron)
        $todosLosIds = array_unique(array_merge($idsActuales, $idsAdicionales));

        foreach ($todosLosIds as $rubroId) {
            $stock = Stock::firstOrCreate([
                'planes_id' => $this->planes_id,
                'rubros_id' => $rubroId,
                'almacenes_id' => $this->almacenes_id,
            ]);

            // Calculamos totales. Si el detalle se borró, este SUM dará 0.
            $totalesDespacho = \DB::table('despachos_detalles')
                ->join('despachos', 'despachos_detalles.despachos_id', '=', 'despachos.id')
                ->where('despachos.planes_id', $this->planes_id)
                ->where('despachos_detalles.rubros_id', $rubroId)
                ->whereNull('despachos.deleted_at')
                /*->selectRaw("
                SUM(CASE WHEN tipo_adquisicion = 'asignacion' THEN cantidad_unidades ELSE 0 END) as asig_cant,
                SUM(CASE WHEN tipo_adquisicion = 'asignacion' THEN (cantidad_unidades * peso_unitario) ELSE 0 END) as asig_peso,
                SUM(CASE WHEN tipo_adquisicion = 'propia' THEN cantidad_unidades ELSE 0 END) as prop_cant,
                SUM(CASE WHEN tipo_adquisicion = 'propia' THEN (cantidad_unidades * peso_unitario) ELSE 0 END) as prop_peso
                ")*/
                ->selectRaw("
                    SUM(CASE WHEN tipo_adquisicion = 'asignacion' THEN cantidad_unidades ELSE 0 END) as asig_cant,
                    SUM(CASE WHEN tipo_adquisicion = 'asignacion' THEN total ELSE 0 END) as asig_peso,
                    SUM(CASE WHEN tipo_adquisicion = 'propia' THEN cantidad_unidades ELSE 0 END) as prop_cant,
                    SUM(CASE WHEN tipo_adquisicion = 'propia' THEN total ELSE 0 END) as prop_peso
                ")
                ->first();

            $asigCant = $totalesDespacho->asig_cant ?? 0;
            $asigPeso = $totalesDespacho->asig_peso ?? 0;
            $propCant = $totalesDespacho->prop_cant ?? 0;
            $propPeso = $totalesDespacho->prop_peso ?? 0;
            $despachoPesoTotal = $asigPeso + $propPeso;

            $stock->update([
                'despacho_asignacion_cantidad' => $asigCant,
                'despacho_asignacion_total' => $asigPeso,
                'despacho_propia_cantidad' => $propCant,
                'despacho_propia_total' => $propPeso,
                'despacho_total' => $despachoPesoTotal,
                // Recalcular el balance neto
                'stock_cantidad' => ($stock->asignacion_cantidad + $stock->propia_cantidad) - ($asigCant + $propCant),
                'stock_total' => $stock->total - $despachoPesoTotal,
            ]);
        }
    }
}
