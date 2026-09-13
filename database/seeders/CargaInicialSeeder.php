<?php

namespace Database\Seeders;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CargaInicialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @throws FileNotFoundException
     */
    public function run(): void
    {
        if (config('app.data_init')) {
            $filePath = storage_path('app/private/data/data_alguarisa_almacen.sql');

            if (File::exists($filePath)) {
                DB::unprepared(File::get($filePath));
            } else {
                // Opcional: registrar una advertencia en los logs para depuración
                logger()->warning("No se pudo ejecutar la inicialización de datos. El archivo no existe en: {$filePath}");
            }
        }
    }
}
