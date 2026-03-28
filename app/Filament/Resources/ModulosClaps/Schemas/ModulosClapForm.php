<?php

namespace App\Filament\Resources\ModulosClaps\Schemas;

use App\Traits\AlmacenSchemas;
use Filament\Schemas\Schema;

class ModulosClapForm
{
    use AlmacenSchemas;

    public static function configure(Schema $schema): Schema
    {
        self::$recepcion = false;
        self::$plan = self::getPlan('MC');
        self::$repeatRelation = 'detalles';
        return $schema
            ->components([
                self::sectionDatos(),
                self::sectionResponsable(),
                self::sectionRubros(),
                self::sectionObservacion(),
            ]);
    }
}
