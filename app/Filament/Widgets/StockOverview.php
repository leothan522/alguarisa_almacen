<?php

namespace App\Filament\Widgets;

use App\Models\Almacen;
use App\Models\Plan;
use App\Models\Stock;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StockOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // 1. Buscamos el Plan por su código interno y el Almacén principal
        $plan = Plan::where('codigo', 'BM')->first();
        $almacenPrincipal = Almacen::where('is_main', 1)->first();

        // Si no existen, evitamos errores devolviendo un array vacío o stats por defecto
        if (! $plan || ! $almacenPrincipal) {
            return [
                Stat::make('Información', 'Datos no disponibles')
                    ->description('Asegúrese de tener un almacén principal y el plan BM configurado.')
                    ->color('gray'),
            ];
        }

        // 2. Consulta de Stock optimizada para Almacén Principal + Plan Bm
        $query = Stock::where('almacenes_id', $almacenPrincipal->id)
            ->where('planes_id', $plan->id);

        // 3. Cálculos de los totales
        $totalGeneral = $query->sum('total');
        $totalAsignacion = $query->sum('asignacion_total');
        $totalPropia = $query->sum('propia_total');
        // Calculamos el total de unidades físicas para el plan
        $unidadesTotales = $query->sum('asignacion_cantidad') + $query->sum('propia_cantidad');

        return [
            // Stat Principal con el nombre del Plan y el del Almacén
            Stat::make($plan->nombre, formatoMillares($totalGeneral).' '.($plan->unidad_medida ?? 'UND'))
                ->description("{$almacenPrincipal->nombre} (".formatoMillares($unidadesTotales, 0).' UND)')
                ->descriptionIcon(Heroicon::OutlinedHome)
                ->color('primary')
                ->chart([5, 8, 12, 10, 20, 15, 25]),

            Stat::make('Asignación', formatoMillares($totalAsignacion).' '.($plan->unidad_medida ?? 'UND'))
                ->description(formatoMillares($query->sum('asignacion_cantidad'), 0).' Unidades asignadas')
                ->descriptionIcon(Heroicon::OutlinedArrowDownTray)
                ->color('info'),

            Stat::make('Propio', formatoMillares($totalPropia).' '.($plan->unidad_medida ?? 'UND'))
                ->description(formatoMillares($query->sum('propia_cantidad'), 0).' Unidades compradas')
                ->descriptionIcon(Heroicon::OutlinedBanknotes)
                ->color('success'),
        ];
    }
}
