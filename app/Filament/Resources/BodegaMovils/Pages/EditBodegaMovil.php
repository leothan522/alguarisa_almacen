<?php

namespace App\Filament\Resources\BodegaMovils\Pages;

use App\Filament\Resources\BodegaMovils\BodegaMovilResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditBodegaMovil extends EditRecord
{
    protected static string $resource = BodegaMovilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
