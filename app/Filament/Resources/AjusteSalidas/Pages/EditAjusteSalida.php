<?php

namespace App\Filament\Resources\AjusteSalidas\Pages;

use App\Filament\Resources\AjusteSalidas\AjusteSalidaResource;
use App\Filament\Resources\Recepcions\RecepcionResource;
use App\Models\Despacho;
use App\Models\Stock;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAjusteSalida extends EditRecord
{
    protected static string $resource = AjusteSalidaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (Despacho $record) {
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
        // / Esta es la clave:
        // Buscamos todos los registros de stock que pertenecen a este despacho
        // y a este plan, incluso si el detalle ya no existe en 'despachos_detalles'

        $rubrosEnStock = Stock::where('planes_id', $this->record->planes_id)
            ->where('almacenes_id', $this->record->almacenes_id)
            ->pluck('rubros_id')
            ->toArray();

        // Sincronizamos todos los rubros que alguna vez tocaron este stock
        $this->record->sincronizarStock($rubrosEnStock);
    }
}
