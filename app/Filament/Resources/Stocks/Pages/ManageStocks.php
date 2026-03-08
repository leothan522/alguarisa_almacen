<?php

namespace App\Filament\Resources\Stocks\Pages;

use App\Filament\Resources\Stocks\StockResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageStocks extends ManageRecords
{
    protected static string $resource = StockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
