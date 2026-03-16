<?php

namespace App\Filament\Resources\BodegaMovils\Schemas;

use App\Traits\AlmacenSchemas;
use Filament\Schemas\Schema;

class BodegaMovilForm
{
    use AlmacenSchemas;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::sectionDatos(false),
                self::sectionResponsable(),
                self::sectionObservacion(),
            ]);
    }
}
