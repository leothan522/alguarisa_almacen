<?php

namespace App\Filament\Resources\AjusteSalidas\Pages;

use App\Filament\Resources\AjusteSalidas\AjusteSalidaResource;
use App\Filament\Resources\Recepcions\RecepcionResource;
use App\Models\Parametro;
use Filament\Resources\Pages\CreateRecord;

class CreateAjusteSalida extends CreateRecord
{
    protected static string $resource = AjusteSalidaResource::class;

    protected static bool $canCreateAnother = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['is_adjustment'] = true;

        return RecepcionResource::dataPersonalizada($data);
    }

    protected function afterCreate(): void
    {
        // $this->record es la instancia de Recepcion recién creada
        $this->record->sincronizarStock();
        $parametro = Parametro::where('nombre', 'numero_salida')->first();
        if ($parametro) {
            $parametro->increment('valor_id');
            $parametro->save();
        } else {
            Parametro::create([
                'nombre' => 'numero_salida',
                'valor_id' => 2,
            ]);
        }
    }

    protected function afterSave(): void
    {
        $this->record->sincronizarStock();
    }
}
