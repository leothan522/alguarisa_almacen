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

    public function sincronizarStock(): void
    {
        // Obtenemos los rubros directamente de la tabla de items para este registro
        $rubrosIds = \DB::table('recepciones_items')
            ->where('recepciones_id', $this->id) // Usamos el nombre correcto de tu FK
            ->pluck('rubros_id')
            ->unique();

        foreach ($rubrosIds as $rubroId) {
            // Buscamos o creamos el Stock por Plan y Rubro
            $stock = \App\Models\Stock::firstOrNew([
                'planes_id' => $this->planes_id,
                'rubros_id' => $rubroId,
                'almacenes_id' => $this->almacenes_id ?? 1,
            ]);

            // Cálculo sumando TODOS los movimientos de este Rubro en este Plan
            $totales = \DB::table('recepciones_items')
                ->join('recepciones', 'recepciones_items.recepciones_id', '=', 'recepciones.id')
                ->where('recepciones.planes_id', $this->planes_id)
                ->where('recepciones_items.rubros_id', $rubroId)
                ->whereNull('recepciones.deleted_at')
                ->selectRaw("
                SUM(CASE WHEN tipo_adquisicion = 'asignacion' THEN cantidad_unidades ELSE 0 END) as asig_cant,
                SUM(CASE WHEN tipo_adquisicion = 'asignacion' THEN (cantidad_unidades * peso_unitario) ELSE 0 END) as asig_total,
                SUM(CASE WHEN tipo_adquisicion = 'propia' THEN cantidad_unidades ELSE 0 END) as prop_cant,
                SUM(CASE WHEN tipo_adquisicion = 'propia' THEN (cantidad_unidades * peso_unitario) ELSE 0 END) as prop_total
            ")
                ->first();

            // Mapeo exacto a tus columnas de la tabla 'stocks'
            $stock->fill([
                'asignacion_cantidad' => $totales->asig_cant ?? 0,
                'asignacion_total' => $totales->asig_total ?? 0,
                'propia_cantidad' => $totales->prop_cant ?? 0,
                'propia_total' => $totales->prop_total ?? 0,
                'total' => ($totales->asig_total ?? 0) + ($totales->prop_total ?? 0),
            ])->save();
        }
    }
}
