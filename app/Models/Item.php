<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    protected $table = 'recepciones_items';

    protected $fillable = [
        'recepciones_id',
        'rubros_id',
        'rubros_nombre',
        'rubros_unidad_medida',
        'cantidad_unidades',
        'peso_unitario',
        'total',
        'fecha_fabricacion',
        'fecha_vencimiento',
        'tipo_adquisicion',
    ];

    public function recepcion(): BelongsTo
    {
        return $this->belongsTo(Recepcion::class, 'recepciones_id', 'id');
    }

    public function rubro(): BelongsTo
    {
        return $this->belongsTo(Rubro::class, 'rubros_id', 'id');
    }
}
