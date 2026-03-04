<?php

namespace App\Filament\Resources\Recepcions\Pages;

use App\Filament\Resources\Recepcions\RecepcionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditRecepcion extends EditRecord
{
    protected static string $resource = RecepcionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
