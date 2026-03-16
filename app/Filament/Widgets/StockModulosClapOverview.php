<?php

namespace App\Filament\Widgets;

use App\Traits\CalcularStockTrait;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StockModulosClapOverview extends StatsOverviewWidget
{
    use CalcularStockTrait;

    protected static bool $isLazy = false;

    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $this->codigoPlan = 'MC';
        $this->calcularStock();

        // Si no existen, evitamos errores devolviendo un array vacío o stats por defecto
        if ($this->noExiste) {
            return [
                Stat::make('Información', 'Datos no disponibles')
                    ->description('Asegúrese de tener un almacén principal y el plan MC configurado.')
                    ->color('gray'),
            ];
        }

        return [
            // Stat Principal con el nombre del Plan y el del Almacén
            Stat::make($this->plan->nombre ?? '', formatoMillares($this->unidadesTotales, 0).' '.($this->plan->unidad_medida ?? 'UND'))
                ->description("{$this->almacen->nombre}")
                ->descriptionIcon(Heroicon::OutlinedHome)
                ->color('primary')
                ->chart([5, 8, 12, 10, 20, 15, 25])
                ->url(route('filament.dashboard.resources.stocks.index').'?filters[planes_id][value]=2'),
        ];
    }
}
