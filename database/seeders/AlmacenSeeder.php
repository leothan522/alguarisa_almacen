<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\Recepcion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Seeder;

class AlmacenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Almacen::create([
            'nombre' => 'Almacen Principal',
            'is_main' => true,
        ]);
    }
}
