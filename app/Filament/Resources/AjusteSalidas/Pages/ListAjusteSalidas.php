<?php

namespace App\Filament\Resources\AjusteSalidas\Pages;

use App\Filament\Resources\AjusteSalidas\AjusteSalidaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAjusteSalidas extends ListRecords
{
    protected static string $resource = AjusteSalidaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
