<?php

namespace App\Filament\Resources\Recepcions\Pages;

use App\Filament\Resources\Recepcions\RecepcionResource;
use App\Models\Almacen;
use App\Models\Jefe;
use App\Models\Parametro;
use App\Models\Responsable;
use Filament\Resources\Pages\CreateRecord;

class CreateRecepcion extends CreateRecord
{
    protected static string $resource = RecepcionResource::class;

    protected static bool $canCreateAnother = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return RecepcionResource::dataPersonalizada($data);
    }

    protected function afterCreate(): void
    {
        // $this->record es la instancia de Recepcion recién creada
        $this->record->sincronizarStock();
        $parametro = Parametro::where('nombre', 'numero_recepcion')->first();
        if ($parametro) {
            $parametro->increment('valor_id');
            $parametro->save();
        } else {
            Parametro::create([
                'nombre' => 'numero_recepcion',
                'valor_id' => 2,
            ]);
        }
    }

    protected function afterSave(): void
    {
        $this->record->sincronizarStock();
    }
}
