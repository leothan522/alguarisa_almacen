<?php

namespace App\Filament\Resources\ModulosClaps\Pages;

use App\Filament\Resources\ModulosClaps\ModulosClapResource;
use App\Traits\MermaTrait;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListModulosClaps extends ListRecords
{
    use MermaTrait;

    protected static string $resource = ModulosClapResource::class;

    protected function getHeaderActions(): array
    {
        self::$plan = 'MC';
        return [
            CreateAction::make(),
            self::actionDespacharMerma(),
        ];
    }
}
