<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rubro extends Model
{
    use SoftDeletes;
    protected $table = 'rubros';
    protected $fillable = ['nombre', 'peso_unitario', 'unidad_medida'];

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'rubros_id', 'id');
    }

}
