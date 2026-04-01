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
        'is_sealed',
        'is_complete',
        'image_documento',
        'image_1',
        'image_2',
        'pdf_expediente',
        'is_adjustment',
        'parent_id',
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

    public function despachoOriginal(): BelongsTo
    {
        return $this->belongsTo(Despacho::class, 'parent_id');
    }

    public function devoluciones(): HasMany
    {
        return $this->hasMany(Despacho::class, 'parent_id');
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
                /*->selectRaw("
                    SUM(CASE WHEN tipo_adquisicion = 'asignacion' THEN cantidad_unidades ELSE 0 END) as asig_cant,
                    SUM(CASE WHEN tipo_adquisicion = 'asignacion' THEN total ELSE 0 END) as asig_peso,
                    SUM(CASE WHEN tipo_adquisicion != 'asignacion' THEN cantidad_unidades ELSE 0 END) as prop_cant,
                    SUM(CASE WHEN tipo_adquisicion != 'asignacion' THEN total ELSE 0 END) as prop_peso
                ")*/
                ->selectRaw("
                        SUM(CASE
                            WHEN tipo_adquisicion = 'asignacion'
                            THEN (CASE WHEN is_return THEN -cantidad_unidades ELSE cantidad_unidades END)
                            ELSE 0 END) as asig_cant,
                        SUM(CASE
                            WHEN tipo_adquisicion = 'asignacion'
                            THEN (CASE WHEN is_return THEN -total ELSE total END)
                            ELSE 0 END) as asig_peso,
                        SUM(CASE
                            WHEN tipo_adquisicion != 'asignacion'
                            THEN (CASE WHEN is_return THEN -cantidad_unidades ELSE cantidad_unidades END)
                            ELSE 0 END) as prop_cant,
                        SUM(CASE
                            WHEN tipo_adquisicion != 'asignacion'
                            THEN (CASE WHEN is_return THEN -total ELSE total END)
                            ELSE 0 END) as prop_peso
                    ")
                ->first();

            /*$asigCant = $totalesDespacho->asig_cant ?? 0;
            $asigPeso = $totalesDespacho->asig_peso ?? 0;
            $propCant = $totalesDespacho->prop_cant ?? 0;
            $propPeso = $totalesDespacho->prop_peso ?? 0;
            $despachoPesoTotal = $asigPeso + $propPeso;

            $factor = $this->is_return ? -1 : 1; // Si es devolución, multiplicamos por -1 para que la resta sea una suma

            $stock->update([
                'despacho_asignacion_cantidad' => $asigCant,
                'despacho_asignacion_total' => $asigPeso,
                'despacho_propia_cantidad' => $propCant,
                'despacho_propia_total' => $propPeso,
                'despacho_total' => $despachoPesoTotal,
                // Recalcular el balance neto
                //                'stock_cantidad' => ($stock->asignacion_cantidad + $stock->propia_cantidad) - ($asigCant + $propCant),
                //                'stock_total' => $stock->total - $despachoPesoTotal,
                'stock_cantidad' => ($stock->asignacion_cantidad + $stock->propia_cantidad) - (($asigCant + $propCant) * $factor),
                'stock_total' => $stock->total - ($despachoPesoTotal * $factor),
            ]);*/

            $asigCant = $totalesDespacho->asig_cant ?? 0;
            $asigPeso = $totalesDespacho->asig_peso ?? 0;
            $propCant = $totalesDespacho->prop_cant ?? 0;
            $propPeso = $totalesDespacho->prop_peso ?? 0;

            $despachoPesoTotal = $asigPeso + $propPeso;
            $despachoCantTotal = $asigCant + $propCant;

            // Actualizamos el stock
            // El stock_total es: (Lo que entró) - (Lo que salió neto)
            // Como las devoluciones ya restan en $despachoPesoTotal, la resta simple funciona.
            $stock->update([
                'despacho_asignacion_cantidad' => $asigCant,
                'despacho_asignacion_total' => $asigPeso,
                'despacho_propia_cantidad' => $propCant,
                'despacho_propia_total' => $propPeso,
                'despacho_total' => $despachoPesoTotal,
                'stock_cantidad' => ($stock->asignacion_cantidad + $stock->propia_cantidad) - $despachoCantTotal,
                'stock_total' => ($stock->asignacion_total + $stock->propia_total) - $despachoPesoTotal,
            ]);
        }
    }

    public function getVentasNetasParaImprimir()
    {
        // 1. Agrupar los rubros del despacho original
        // Usamos el rubros_id como llave para poder restar fácilmente
        $totales = $this->detalles->groupBy('rubros_id')->map(function ($items) {
            return [
                'nombre' => $items->first()->rubros_nombre,
                'unidad' => $items->first()->rubros_unidad_medida,
                'tipo' => $items->first()->tipo_adquisicion,
                'cantidad' => $items->sum('cantidad_unidades'),
                'peso_total' => $items->sum('total'),
            ];
        })->toArray();

        // 2. Restar las devoluciones (despachos hijos con parent_id = este_id)
        $this->devoluciones()->with('detalles')->get()->each(function ($devolucion) use (&$totales) {
            foreach ($devolucion->detalles as $detalle) {
                if (isset($totales[$detalle->rubros_id])) {
                    $totales[$detalle->rubros_id]['cantidad'] -= $detalle->cantidad_unidades;
                    $totales[$detalle->rubros_id]['peso_total'] -= $detalle->total;
                }
            }
        });

        // 3. Filtrar los que quedaron en cero y convertir a objeto para FPDF
        return collect($totales)
            ->filter(fn ($item) => $item['cantidad'] > 0)
            ->map(fn ($item) => (object) $item);
    }
}
