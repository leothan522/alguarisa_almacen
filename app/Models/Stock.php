<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends Model
{
    use SoftDeletes;
    protected $table = 'stocks';
    protected $fillable = [
        'almacenes_id',
        'planes_id',
        'rubros_id',
        'asignacion_cantidad',
        'asignacion_peso',
        'asignacion_total',
        'propia_cantidad',
        'propia_peso',
        'propia_total',
        'total',
    ];

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacenes_id', 'id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'planes_id', 'id');
    }

    public function rubro(): BelongsTo
    {
        return $this->belongsTo(Rubro::class, 'rubros_id', 'id');
    }

}
