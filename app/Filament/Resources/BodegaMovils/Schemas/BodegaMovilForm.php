<?php

namespace App\Filament\Resources\BodegaMovils\Schemas;

use App\Traits\AlmacenSchemas;
use Filament\Schemas\Schema;

class BodegaMovilForm
{
    use AlmacenSchemas;

    public static function configure(Schema $schema): Schema
    {
        self::$recepcion = false;
        self::$plan = self::getPlan('BM');

        return $schema
            ->components([
                self::sectionDatos(),
                self::sectionResponsable(),
                self::sectionObservacion(),
            ]);
    }
}
