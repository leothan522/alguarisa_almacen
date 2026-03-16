<?php

namespace App\Filament\Resources\Recepcions\Schemas;

use App\Models\Rubro;
use App\Traits\AlmacenSchemas;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Support\Str;

class RecepcionForm
{
    use AlmacenSchemas;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::sectionDatos(),
                self::sectionResponsable(),
                self::sectionRubros(),
                self::sectionObservacion(),
            ]);
    }

}
