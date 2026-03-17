<?php

namespace App\Filament\Resources\Recepcions\Pages;

use App\Filament\Resources\Recepcions\RecepcionResource;
use App\Models\Recepcion;
use App\Models\Stock;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

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
        // Obtenemos todos los rubros que ya existen en el stock para este plan/almacén
        // Esto garantiza que si un rubro fue borrado del repeater, se recalcule a 0
        $rubrosEnStock = Stock::where('planes_id', $this->record->planes_id)
            ->where('almacenes_id', $this->record->almacenes_id)
            ->pluck('rubros_id')
            ->toArray();

        $this->record->sincronizarStock($rubrosEnStock);
    }
}
