<?php

namespace App\Filament\Resources\BodegaMovils\Pages;

use App\Filament\Resources\BodegaMovils\BodegaMovilResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBodegaMovils extends ListRecords
{
    protected static string $resource = BodegaMovilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
