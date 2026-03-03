<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plan::create([
            'nombre' => 'Bodega móvil',
            'unidad_medida' => 'KG',
            'cuspal' => true,
        ]);

        Plan::create([
            'nombre' => 'Módulos CLAP',
            'unidad_medida' => 'UND',
        ]);
    }
}
