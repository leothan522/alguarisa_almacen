<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use SoftDeletes;

    protected $table = 'planes';

    protected $fillable = [
        'codigo',
        'nombre',
        'unidad_medida',
        'cuspal'];

    public function recepciones(): HasMany
    {
        return $this->hasMany(Recepcion::class, 'planes_id', 'id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'planes_id', 'id');
    }
}
