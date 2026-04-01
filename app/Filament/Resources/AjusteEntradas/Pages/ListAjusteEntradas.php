<?php

namespace App\Filament\Resources\AjusteEntradas\Pages;

use App\Filament\Resources\AjusteEntradas\AjusteEntradaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAjusteEntradas extends ListRecords
{
    protected static string $resource = AjusteEntradaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
