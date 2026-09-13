<?php

namespace App\Filament\Widgets;

use App\Models\Plan;
use App\Traits\CalcularStockTrait;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StockOverview extends StatsOverviewWidget
{
    use CalcularStockTrait;

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        /*$this->codigoPlan = 'BM';
        $this->calcularStock();

        // Si no existen, evitamos errores devolviendo un array vacío o stats por defecto
        if ($this->noExiste) {
            return [
                Stat::make('Información', 'Datos no disponibles')
                    ->description('Asegúrese de tener un almacén principal y el plan BM configurado.')
                    ->color('gray'),
            ];
        }

        return [
            // Stat Principal con el nombre del Plan y el del Almacén
            Stat::make($this->plan->nombre, formatoMillares($this->totalGeneral).' '.($this->plan->unidad_medida ?? 'UND'))
                ->description("{$this->almacen->nombre} (".formatoMillares($this->unidadesTotales, 0).' UND)')
                ->descriptionIcon(Heroicon::OutlinedHome)
                ->color('primary')
                ->chart([5, 8, 12, 10, 20, 15, 25])
                ->url(route('filament.dashboard.resources.stocks.index').'?filters[planes_id][value]=1'),

            Stat::make('Asignación', formatoMillares($this->totalAsignacion).' '.($this->plan->unidad_medida ?? 'UND'))
                ->description(formatoMillares($this->cantidadAsignacion, 0).' Unidades asignadas')
                ->descriptionIcon(Heroicon::OutlinedArrowDownTray)
                ->color('info'),

            Stat::make('Propio', formatoMillares($this->totalPropia).' '.($this->plan->unidad_medida ?? 'UND'))
                ->description(formatoMillares($this->cantidadPropia, 0).' Unidades compradas')
                ->descriptionIcon(Heroicon::OutlinedBanknotes)
                ->color('success'),
        ];*/

        $this->codigoPlan = 'BM';
        $this->calcularStock();

        if ($this->noExiste) {
            return [
                Stat::make('Información', 'Datos no disponibles')
                    ->description('Asegúrese de tener un almacén principal y el plan BM configurado.')
                    ->color('gray'),
            ];
        }

        $urlFiltrada = route('filament.dashboard.resources.stocks.index').'?filters[planes_id][value]='.($this->plan->id ?? '');

        return [
            // Stat Principal: Entero en KG
            Stat::make(
                $this->plan->nombre,
                formatoMillares(round($this->totalGeneral), 0).' KG'
            )
                ->description("{$this->almacen->nombre} (".formatoMillares($this->unidadesTotales, 0).' UND)')
                ->descriptionIcon(Heroicon::OutlinedHome)
                ->color('primary')
                ->chart([5, 8, 12, 10, 20, 15, 25])
                ->url($urlFiltrada),

            // Asignación
            Stat::make(
                'Asignación',
                formatoMillares(round($this->totalAsignacion), 0).' KG'
            )
                ->description(formatoMillares($this->cantidadAsignacion, 0).' Unidades asignadas')
                ->descriptionIcon(Heroicon::OutlinedArrowDownTray)
                ->color('info'),

            // Propio
            Stat::make(
                'Propio',
                formatoMillares(round($this->totalPropia), 0).' KG'
            )
                ->description(formatoMillares($this->cantidadPropia, 0).' Unidades compradas')
                ->descriptionIcon(Heroicon::OutlinedBanknotes)
                ->color('success'),
        ];
    }
}
