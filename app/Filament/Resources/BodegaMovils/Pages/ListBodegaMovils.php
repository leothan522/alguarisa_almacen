<?php

namespace App\Filament\Resources\BodegaMovils\Pages;

use App\Filament\Resources\BodegaMovils\BodegaMovilResource;
use App\Filament\Resources\Recepcions\RecepcionResource;
use App\Models\Almacen;
use App\Models\Despacho;
use App\Models\Detalle;
use App\Models\Parametro;
use App\Models\Plan;
use App\Models\Responsable;
use App\Models\Rubro;
use App\Models\Stock;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Illuminate\Support\Str;

class ListBodegaMovils extends ListRecords
{
    protected static string $resource = BodegaMovilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            self::actionDespacharMerma(),
        ];
    }

    protected static function actionDespacharMerma()
    {
        return Action::make('merma-sacar')
            ->label('Despachar Merma')
            ->color('gray')
            ->schema([
                Select::make('responsables_id')
                    ->label('¿Quien recibe?')
                    ->options(Responsable::query()->pluck('nombre', 'id')->map(fn ($nombre) => Str::upper($nombre)))
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('rubros_id')
                    ->label('Rubro')
                    ->options(Rubro::query()->pluck('nombre', 'id')->map(fn ($nombre) => Str::upper($nombre)))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live(),
                TextInput::make('total')
                    ->label('Peso Total')
                    ->numeric()
                    ->step(0.01)
                    ->required()
                    ->live(onBlur: true)
                    ->rules(self::rulesPeso()),
                Select::make('tipo_adquisicion')
                    ->label('Tipo adquisición')
                    ->options([
                        'asignacion' => 'ASIGNACIÓN',
                        'propia' => 'PROPIA',
                    ])
                    ->required()
                    ->live(),
                Textarea::make('observacion')
                    ->label('Observación')
                    ->required(),
            ])
            ->action(function (array $data): void {

                $numero = self::getNumero();
                $fecha = now();
                $hora = $fecha->toDateTimeString();
                $observacion = $data['observacion'];
                $almacenes_id = self::getAlmacen();
                $planes_id = self::getPlan();
                $data = RecepcionResource::dataPersonalizada($data);

                $despacho = new Despacho;
                $despacho->numero = $numero;
                $despacho->fecha = $fecha;
                $despacho->hora = $hora;
                $despacho->observacion = $observacion;
                $despacho->almacenes_id = $almacenes_id;
                $despacho->planes_id = $planes_id;
                $despacho->jefes_id = $data['jefes_id'];
                $despacho->jefes_nombre = $data['jefes_nombre'];
                $despacho->jefes_cedula = $data['jefes_cedula'];
                $despacho->responsables_id = $data['responsables_id'];
                $despacho->responsables_nombre = $data['responsables_nombre'];
                $despacho->responsables_cedula = $data['responsables_cedula'];
                $despacho->responsables_telefono = $data['responsables_telefono'];
                $despacho->responsables_empresa = $data['responsables_empresa'];
                $despacho->is_merma = true;
                $despacho->save();

                $rubro = Rubro::find($data['rubros_id']);

                Detalle::create([
                    'despachos_id' => $despacho->id,
                    'rubros_id' => $rubro->id,
                    'rubros_nombre' => $rubro->nombre,
                    'rubros_unidad_medida' => $rubro->unidad_medida,
                    'cantidad_unidades' => 0,
                    'peso_unitario' => $data['total'],
                    'total' => $data['total'],
                    'tipo_adquisicion' => $data['tipo_adquisicion'],
                ]);
                $despacho->refresh();
                // Llamamos al método centralizado para que recalcule todo correctamente
                $despacho->sincronizarStock();
            })
            ->modalWidth(Width::Small);
    }

    protected static function getNumero(): string
    {
        $num = 1;
        $formato = '';
        $parametro = Parametro::where('nombre', 'numero_despacho')->first();
        if ($parametro) {
            $formato = $parametro->valor_texto;
            $num = $parametro->valor_id > 0 ? $parametro->valor_id : $num;
        }
        $i = 0;
        do {
            $num = $num + $i;
            $codigo = $formato.cerosIzquierda($num, numSizeCodigo());
            $existe = Despacho::where('numero', $codigo)->exists();
            $i++;
        } while ($existe);

        return $codigo;
    }

    protected static function rulesPeso(): array
    {
        return [
            fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                $rubroId = $get('rubros_id');
                $almacenId = self::getAlmacen();
                $planId = self::getPlan();
                $tipoAdquisicion = $get('tipo_adquisicion');
                $total = $get('total');

                if (! $rubroId) {
                    return;
                }

                // Buscamos el registro de stock para ese rubro y almacén
                $stock = Stock::where('rubros_id', $rubroId)
                    ->where('planes_id', $planId)
                    ->where('almacenes_id', $almacenId)
                    ->first();

                $disponible = 0.00; // Inicializamos como float

                if ($stock) {
                    if ($tipoAdquisicion == 'asignacion') {
                        // Calculamos y redondeamos a 2 decimales
                        $disponible = round($stock->asignacion_total - $stock->despacho_asignacion_total, 2);
                    } else {
                        $disponible = round($stock->propia_total - $stock->despacho_propia_total, 2);
                    }
                }

                if ($total > $disponible) {
                    $unidad = $stock?->rubro?->unidad_medida ?? '';
                    $disponibleFormateado = number_format($disponible, 2, ',', '.');

                    $fail("Stock insuficiente. Hay {$disponibleFormateado} {$unidad} disponibles.");
                }
            },
        ];
    }

    protected static function getAlmacen()
    {
        return Almacen::where('is_main', 1)->first()?->id;
    }

    protected static function getPlan()
    {
        return Plan::where('codigo', 'BM')->first()?->id;
    }
}
