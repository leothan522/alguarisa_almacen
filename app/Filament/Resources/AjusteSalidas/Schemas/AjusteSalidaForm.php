<?php

namespace App\Filament\Resources\AjusteSalidas\Schemas;

use App\Traits\AlmacenSchemas;
use Filament\Schemas\Schema;

class AjusteSalidaForm
{
    use AlmacenSchemas;

    public static function configure(Schema $schema): Schema
    {
        self::$recepcion = false;
        self::$repeatRelation = 'detalles';
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
