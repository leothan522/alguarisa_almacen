<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'asignacion_total',
        'propia_cantidad',
        'propia_total',
        'total',
        'despacho_asignacion_cantidad',
        'despacho_asignacion_total',
        'despacho_propia_cantidad',
        'despacho_propia_total',
        'despacho_total',
        'stock_cantidad',
        'stock_total',
    ];

    protected function casts(): array
    {
        return [
            'asignacion_total' => 'decimal:3',
            'propia_total' => 'decimal:3',
            'total' => 'decimal:3',
            'despacho_asignacion_total' => 'decimal:3',
            'despacho_propia_total' => 'decimal:3',
            'despacho_total' => 'decimal:3',
            'stock_total' => 'decimal:3',
        ];
    }

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

    public function fullAsignacion(): Attribute
    {
        return Attribute::make(
            get: fn () => round((float) $this->asignacion_total - (float) $this->despacho_asignacion_total, 3)
        );
    }

    public function fullPropia(): Attribute
    {
        return Attribute::make(
            get: fn () => round((float) $this->propia_total - (float) $this->despacho_propia_total, 3)
        );
    }

    public function undAsignacion(): Attribute
    {
        return Attribute::make(get: fn () => $this->asignacion_cantidad - $this->despacho_asignacion_cantidad);
    }

    public function undPropia(): Attribute
    {
        return Attribute::make(get: fn () => $this->propia_cantidad - $this->despacho_propia_cantidad);
    }
}
