<?php

namespace App\Filament\Resources\AjusteEntradas\Schemas;

use App\Traits\AlmacenSchemas;
use Filament\Schemas\Schema;

class AjusteEntradaForm
{
    use AlmacenSchemas;

    public static function configure(Schema $schema): Schema
    {
        self::$ajuste = true;
        return $schema
            ->components([
                self::sectionDatos(),
                self::sectionResponsable(),
                self::sectionRubros(),
                self::sectionObservacion(),
            ]);
    }
}
