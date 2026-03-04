<?php

namespace App\Filament\Resources\Recepcions\Pages;

use App\Filament\Resources\Recepcions\RecepcionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRecepcions extends ListRecords
{
    protected static string $resource = RecepcionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
