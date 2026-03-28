<?php

namespace App\Filament\Resources\ModulosClaps\Pages;

use App\Filament\Resources\ModulosClaps\ModulosClapResource;
use App\Filament\Resources\Recepcions\RecepcionResource;
use App\Models\Parametro;
use Filament\Resources\Pages\CreateRecord;

class CreateModulosClap extends CreateRecord
{
    protected static string $resource = ModulosClapResource::class;

    protected static bool $canCreateAnother = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return RecepcionResource::dataPersonalizada($data);
    }

    protected function afterCreate(): void
    {
        // $this->record es la instancia de Recepcion recién creada
        $this->record->sincronizarStock();
        $parametro = Parametro::where('nombre', 'numero_despacho')->first();
        if ($parametro) {
            $parametro->increment('valor_id');
            $parametro->save();
        } else {
            Parametro::create([
                'nombre' => 'numero_despacho',
                'valor_id' => 2,
            ]);
        }
    }

    protected function afterSave(): void
    {
        $this->record->sincronizarStock();
    }
}
