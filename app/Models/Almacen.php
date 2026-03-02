<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Almacen extends Model
{
    use SoftDeletes;

    protected $table = 'almacenes';

    protected $fillable = ['nombre', 'is_main'];

    public function recepciones(): HasMany
    {
        return $this->hasMany(Recepcion::class, 'almacenes_id', 'id');
    }
}
