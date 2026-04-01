<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recepcion extends Model
{
    use SoftDeletes;

    protected $table = 'recepciones';

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
        'is_sealed',
        'is_complete',
        'image_documento',
        'image_1',
        'image_2',
        'pdf_expediente',
        'is_adjustment',
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

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'recepciones_id', 'id');
    }

    public function sincronizarStock(?array $idsManuales = []): void
    {
        // Capturamos rubros de items y mermas actuales
        $rubrosItems = \DB::table('recepciones_items')
            ->where('recepciones_id', $this->id)
            ->pluck('rubros_id');

        $rubrosMermas = \DB::table('recepciones_mermas')
            ->where('recepciones_id', $this->id)
            ->pluck('rubros_id');

        // Unimos con los IDs manuales (los que pudieron ser borrados)
        $rubrosIds = $rubrosItems->merge($rubrosMermas)
            ->merge($idsManuales)
            ->unique()
            ->filter(); // Elimina nulos

        foreach ($rubrosIds as $rubroId) {
            $stock = Stock::firstOrCreate([
                'planes_id' => $this->planes_id,
                'rubros_id' => $rubroId,
                'almacenes_id' => $this->almacenes_id ?? 1,
            ]);

            // 1. Totales de Items
            $totalesItems = \DB::table('recepciones_items')
                ->join('recepciones', 'recepciones_items.recepciones_id', '=', 'recepciones.id')
                ->where('recepciones.planes_id', $this->planes_id)
                ->where('recepciones_items.rubros_id', $rubroId)
                ->whereNull('recepciones.deleted_at')
                ->selectRaw("
                SUM(CASE WHEN tipo_adquisicion = 'asignacion' THEN cantidad_unidades ELSE 0 END) as asig_cant,
                SUM(CASE WHEN tipo_adquisicion = 'asignacion' THEN (cantidad_unidades * peso_unitario) ELSE 0 END) as asig_peso,
                SUM(CASE WHEN tipo_adquisicion != 'asignacion' THEN cantidad_unidades ELSE 0 END) as prop_cant,
                SUM(CASE WHEN tipo_adquisicion != 'asignacion' THEN (cantidad_unidades * peso_unitario) ELSE 0 END) as prop_peso
            ")
                ->first();

            // 2. Totales de Mermas
            $totalesMermas = \DB::table('recepciones_mermas')
                ->join('recepciones', 'recepciones_mermas.recepciones_id', '=', 'recepciones.id')
                ->where('recepciones.planes_id', $this->planes_id)
                ->where('recepciones_mermas.rubros_id', $rubroId)
                ->whereNull('recepciones.deleted_at')
                ->selectRaw("
                SUM(CASE WHEN tipo_adquisicion = 'asignacion' THEN total ELSE 0 END) as asig_merma,
                SUM(CASE WHEN tipo_adquisicion != 'asignacion' THEN total ELSE 0 END) as prop_merma
            ")
                ->first();

            $asigCant = $totalesItems->asig_cant ?? 0;
            $propCant = $totalesItems->prop_cant ?? 0;
            $finalAsigPeso = ($totalesItems->asig_peso ?? 0) + ($totalesMermas->asig_merma ?? 0);
            $finalPropPeso = ($totalesItems->prop_peso ?? 0) + ($totalesMermas->prop_merma ?? 0);
            $totalEntradaPeso = $finalAsigPeso + $finalPropPeso;

            $stock->update([
                'asignacion_cantidad' => $asigCant,
                'propia_cantidad' => $propCant,
                'asignacion_total' => $finalAsigPeso,
                'propia_total' => $finalPropPeso,
                'total' => $totalEntradaPeso,

                // BALANCE NETO: Entradas actuales - Despachos registrados
                'stock_cantidad' => ($asigCant + $propCant) - ($stock->despacho_asignacion_cantidad + $stock->despacho_propia_cantidad),
                'stock_total' => $totalEntradaPeso - ($stock->despacho_total ?? 0),
            ]);
        }
    }

    public function getTotalUnidadesAttribute()
    {
        // Asegúrate de que 'items' sea el nombre exacto de la relación
        return $this->items()->sum('cantidad_unidades');
    }

    public function getTotalPesoAttribute()
    {
        return $this->items()->sum('total');
    }

    public function mermas(): HasMany
    {
        return $this->hasMany(Merma::class, 'recepciones_id', 'id');
    }
}
