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
            'codigo' => 'BM',
            'nombre' => 'Bodega móvil',
            'unidad_medida' => 'KG',
            'cuspal' => true,
        ]);

        Plan::create([
            'codigo' => 'MC',
            'nombre' => 'Módulos CLAP',
            'unidad_medida' => 'UND',
        ]);

        Plan::create([
            'codigo' => '001',
            'nombre' => 'MERCAL',
            'unidad_medida' => 'UND',
        ]);

        Plan::create([
            'codigo' => '002',
            'nombre' => 'PDVAL',
            'unidad_medida' => 'UND',
        ]);

        Plan::create([
            'codigo' => '003',
            'nombre' => 'FUNDAPROAL',
            'unidad_medida' => 'UND',
        ]);

        Plan::create([
            'codigo' => '004',
            'nombre' => 'INN',
            'unidad_medida' => 'UND',
        ]);

        Plan::create([
            'codigo' => '005',
            'nombre' => 'Gobernación',
            'unidad_medida' => 'UND',
        ]);


    }
}
