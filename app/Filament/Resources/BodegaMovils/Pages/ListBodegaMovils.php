<?php

namespace App\Filament\Resources\BodegaMovils\Pages;

use App\Filament\Resources\BodegaMovils\BodegaMovilResource;
use App\Traits\MermaTrait;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBodegaMovils extends ListRecords
{
    use MermaTrait;

    protected static string $resource = BodegaMovilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            self::actionDespacharMerma(),
        ];
    }
}
