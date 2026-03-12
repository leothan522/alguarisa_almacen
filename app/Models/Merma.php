<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Merma extends Model
{
    protected $table = 'recepciones_mermas';
    protected $fillable = [
        'recepciones_id',
        'almacenes_id',
        'planes_id',
        'rubros_id',
        'tipo_adquisicion',
        'total',
    ];

    public function recepcion(): BelongsTo
    {
        return $this->belongsTo(Recepcion::class, 'recepciones_id', 'id');
    }

}
