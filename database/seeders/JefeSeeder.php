<?php

namespace Database\Seeders;

use App\Models\Jefe;
use Illuminate\Database\Seeder;

class JefeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Jefe::create([
            'nombre' => 'JORGE L. LADERA P',
            'cedula' => 'V-20.183.087',
            'is_main' => true,
        ]);
    }
}
