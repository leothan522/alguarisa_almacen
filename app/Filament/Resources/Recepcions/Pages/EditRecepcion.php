<?php

namespace App\Filament\Resources\Recepcions\Pages;

use App\Filament\Resources\Recepcions\RecepcionResource;
use App\Models\Recepcion;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditRecepcion extends EditRecord
{
    protected static string $resource = RecepcionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (Recepcion $record) {
                    $numero = '*'.$record->numero;
                    $record->update([
                        'numero' => $numero,
                    ]);
                }),
            ForceDeleteAction::make(),
            RestoreAction::make()
                ->before(function (Recepcion $record) {
                    $numero = Str::replace('*', '', $record->numero);
                    $record->update([
                        'numero' => $numero,
                    ]);
                }),
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
