<?php

namespace App\Filament\Resources\Stocks\Pages;

use App\Exports\MovimientosRubroExport;
use App\Filament\Resources\Stocks\StockResource;
use App\Models\Rubro;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Maatwebsite\Excel\Facades\Excel;

class ManageStocks extends ManageRecords
{
    protected static string $resource = StockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportarMovimientosGlobal')
                ->label('Reporte de Movimientos')
                ->icon(Heroicon::OutlinedDocumentArrowDown)
                ->color('success')
                ->visible(fn (): bool => isAdmin() || auth()->user()->hasRole('almacen'))
                ->modalWidth(Width::Small)
                ->modalHeading('Historial de Movimientos por Rubro')
                ->modalDescription('Selecciona el rubro y el rango de fechas para generar el reporte en Excel.')
                ->schema([
                    Select::make('rubros_id')
                        ->label('Seleccione el Rubro')
                        ->options(Rubro::query()
                            ->pluck('nombre', 'id')
                            ->map(fn ($nombre) => mb_strtoupper($nombre))) // Ajusta 'nombre' si en tu tabla se llama diferente
                        ->searchable()
                        ->required(),

                    DatePicker::make('fecha_desde')
                        ->label('Fecha Desde')
                        ->required()
                        ->default(now()->startOfMonth())
                        ->maxDate(now()),

                    DatePicker::make('fecha_hasta')
                        ->label('Fecha Hasta')
                        ->required()
                        ->default(now())
                        ->maxDate(now()),
                ])
                ->action(function (array $data) {
                    // Buscamos el objeto del rubro para usar su nombre en el título del archivo Excel
                    $rubro = Rubro::find($data['rubros_id']);

                    $nombreArchivo = 'movimientos_'.str($rubro->nombre)->slug('_').'_'.now()->format('Ymd_His').'.xlsx';

                    return Excel::download(
                        new MovimientosRubroExport($data['rubros_id'], $data['fecha_desde'], $data['fecha_hasta']),
                        $nombreArchivo
                    );
                }),
        ];
    }
}
