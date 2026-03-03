<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parametro extends Model
{
    protected $table = 'parametros';
    protected $fillable = ['nombre', 'valor_id', 'valor_texto'];
}
