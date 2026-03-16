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


}
