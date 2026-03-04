<?php

namespace App\Filament\Resources\Recepcions\Pages;

use App\Filament\Resources\Recepcions\RecepcionResource;
use App\Models\Almacen;
use App\Models\Jefe;
use App\Models\Responsable;
use Filament\Resources\Pages\CreateRecord;

class CreateRecepcion extends CreateRecord
{
    protected static string $resource = RecepcionResource::class;

    protected static bool $canCreateAnother = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['almacenes_id'] = Almacen::where('is_main', 1)->first()->id;

        $jefe = Jefe::where('is_main', 1)->first();
        $data['jefes_id'] = $jefe->id;
        $data['jefes_nombre'] = $jefe->nombre;
        $data['jefes_cedula'] = $jefe->cedula;

        $responsable = Responsable::find($data['responsables_id']);
        $data['responsables_nombre'] = $responsable->nombre;
        $data['responsables_cedula'] = $responsable->cedula;
        $data['responsables_telefono'] = $responsable->telefono;
        $data['responsables_empresa'] = $responsable->empresa;

        return $data;
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
