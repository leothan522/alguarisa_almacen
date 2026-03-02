<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jefe extends Model
{
    use SoftDeletes;
    protected $table = 'jefes';
    protected $fillable = ['nombre', 'cedula', 'is_main'];

    public function recepciones(): HasMany
    {
        return $this->hasMany(Recepcion::class, 'jefes_id', 'id');
    }
}
