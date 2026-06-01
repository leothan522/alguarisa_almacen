<?php

namespace App\Exports;

use App\Models\Detalle;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class MovimientosRubroExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithTitle
{
    protected $rubroId;

    protected $fechaDesde;

    protected $fechaHasta;

    public function __construct($rubroId, $fechaDesde, $fechaHasta)
    {
        $this->rubroId = $rubroId;
        $this->fechaDesde = $fechaDesde;
        $this->fechaHasta = $fechaHasta;
    }

    public function collection(): Collection
    {
        // 1. Obtener Entradas (Recepciones)
        $entradas = Item::query()
            ->where('rubros_id', $this->rubroId)
            ->whereHas('recepcion', function ($query) {
                $query->whereBetween('fecha', [$this->fechaDesde, $this->fechaHasta])
                    ->whereNull('deleted_at'); // Respetando el SoftDeletes de la cabecera
            })
            ->with('recepcion')
            ->get()
            ->map(function ($item) {
                return [
                    'fecha' => $item->recepcion->fecha,
                    'documento' => 'RECEPCIÓN #'.$item->recepcion->numero,
                    'tipo_movimiento' => 'ENTRADA',
                    'tipo_adquisicion' => strtoupper($item->tipo_adquisicion),
                    'cantidad' => $item->cantidad_unidades,
                    'peso' => $item->total,
                    'observacion' => $item->recepcion->observacion,
                ];
            });

        // 2. Obtener Salidas (Despachos)
        $salidas = Detalle::query()
            ->where('rubros_id', $this->rubroId)
            ->whereHas('despacho', function ($query) {
                $query->whereBetween('fecha', [$this->fechaDesde, $this->fechaHasta])
                    ->whereNull('deleted_at'); // Respetando el SoftDeletes
            })
            ->with('despacho')
            ->get()
            ->map(function ($detalle) {
                // Si el despacho es una devolución (is_return), la lógica de tu método sincronizarStock
                // nos dice que resta al despacho (es decir, vuelve a entrar al almacén).
                $esDevolucion = $detalle->despacho->is_return;

                return [
                    'fecha' => $detalle->despacho->fecha,
                    'documento' => ($esDevolucion ? 'DEVOLUCIÓN #' : 'DESPACHO #').$detalle->despacho->numero,
                    'tipo_movimiento' => $esDevolucion ? 'DEVOLUCIÓN (ENTRADA)' : 'SALIDA',
                    'tipo_adquisicion' => strtoupper($detalle->tipo_adquisicion),
                    'cantidad' => $detalle->cantidad_unidades,
                    'peso' => $detalle->total,
                    'observacion' => $detalle->despacho->observacion,
                ];
            });

        // 3. Unificar y ordenar cronológicamente por la fecha
        return $entradas->concat($salidas)->sortBy('fecha')->values();
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Documento / Referencia',
            'Tipo Movimiento',
            'Tipo Adquisición',
            'Cantidad (Unidades)',
            'Peso Total (Kg)',
            'Observaciones / Detalles',
        ];
    }

    public function map($movimiento): array
    {
        // Al ser un array mapeado manualmente en la colección, lo leemos directamente
        return [
            // Si viene como string o Carbon, nos aseguramos del formato de fecha d/m/Y
            Carbon::parse($movimiento['fecha'])->format('d/m/Y'),
            $movimiento['documento'],
            $movimiento['tipo_movimiento'],
            $movimiento['tipo_adquisicion'],
            $movimiento['cantidad'],
            $movimiento['peso'],
            $movimiento['observacion'] ?? 'Sin observaciones',
        ];
    }

    public function title(): string
    {
        return Str::upper('Movimientos por Rubro');
    }
}
