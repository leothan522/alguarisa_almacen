<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Responsable extends Model
{
    use SoftDeletes;

    protected $table = 'responsables';

    protected $fillable = [
        'nombre',
        'cedula',
        'telefono',
        'empresa',
    ];

    public function recepciones(): HasMany
    {
        return $this->hasMany(Recepcion::class, 'responsables_id', 'id');
    }

}
