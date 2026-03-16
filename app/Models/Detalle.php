<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Detalle extends Model
{
    protected $table = 'despachos_detalles';
    protected $fillable = [
        'despachos_id',
        'rubros_id',
        'rubros_nombre',
        'rubros_unidad_medida',
        'cantidad_unidades',
        'peso_unitario',
        'total',
        'tipo_adquisicion',
    ];

    public function despacho(): BelongsTo
    {
        return $this->belongsTo(Despacho::class, 'despachos_id', 'id');
    }

    public function rubro(): BelongsTo
    {
        return $this->belongsTo(Rubro::class, 'rubros_id', 'id');
    }

}
