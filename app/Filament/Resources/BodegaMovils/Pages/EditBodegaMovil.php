<?php

namespace App\Filament\Resources\BodegaMovils\Pages;

use App\Filament\Resources\BodegaMovils\BodegaMovilResource;
use App\Filament\Resources\Recepcions\RecepcionResource;
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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return RecepcionResource::dataPersonalizada($data);
    }

    protected function afterCreate(): void
    {
        // $this->record es la instancia de Recepcion recién creada
        $this->record->sincronizarStock();
    }

    protected function afterSave(): void
    {
        $this->record->sincronizarStock();
    }

}
