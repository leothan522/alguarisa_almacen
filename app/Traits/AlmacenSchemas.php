<?php

namespace App\Traits;

use App\Models\Almacen;
use App\Models\Despacho;
use App\Models\Parametro;
use App\Models\Plan;
use App\Models\Recepcion;
use App\Models\Responsable;
use App\Models\Rubro;
use App\Models\Stock;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait AlmacenSchemas
{
    public static bool $recepcion = true;

    public static ?int $plan = null;

    public static string $repeatRelation = 'items';

    public static bool $ajuste = false;

    protected static function sectionDatos()
    {
        return Section::make('Datos Básicos')
            ->schema([
                TextInput::make('numero')
                    ->default(function (): string {
                        $num = 1;
                        $formato = '';
                        if (! self::$ajuste) {
                            $nombre = self::$recepcion ? 'numero_recepcion' : 'numero_despacho';
                        } else {
                            $nombre = self::$recepcion ? 'numero_entrada' : 'numero_salida';
                        }
                        $parametro = Parametro::where('nombre', $nombre)->first();
                        if ($parametro) {
                            $formato = $parametro->valor_texto;
                            $num = $parametro->valor_id > 0 ? $parametro->valor_id : $num;
                        }
                        $i = 0;
                        do {
                            $num = $num + $i;
                            $codigo = $formato.cerosIzquierda($num, numSizeCodigo());
                            if (self::$ajuste) {
                                $codigo = self::$recepcion ? 'ENT-'.$codigo : 'SAL-'.$codigo;
                            }
                            $existe = self::$recepcion ? Recepcion::where('numero', $codigo)->exists() : Despacho::where('numero', $codigo)->exists();
                            $i++;
                        } while ($existe);

                        return $codigo;
                    })
                    ->unique()
                    ->required(),
                Select::make('planes_id')
                    ->label('Plan')
                    ->relationship(name: 'plan', titleAttribute: 'nombre')
                    ->required()
                    ->disabledOn('edit')
                    ->default(self::$plan)
                    ->disableOptionWhen(fn (string $value): bool => (! self::$ajuste && $value != self::$plan) && ! self::$recepcion),
                DatePicker::make('fecha')
                    ->default(now())
                    ->required(),
                TimePicker::make('hora')
                    ->default(now())
                    ->seconds(false)
                    ->required(),
                Hidden::make('almacenes_id')
                    ->default(fn () => Almacen::where('is_main', 1)->first()?->id),
            ])
            ->compact()
            ->columns();
    }

    protected static function sectionResponsable()
    {
        return Section::make(fn (): string => self::$recepcion ? '¿Quien entrega?' : '¿Quien recibe?')
            ->schema([
                Select::make('responsables_id')
                    ->relationship(name: 'responsable', titleAttribute: 'nombre')
                    ->createOptionForm(self::formResponsable())
                    ->createOptionAction(function (Action $action) {
                        return $action->modalWidth(Width::ExtraSmall);
                    })
                    /*->editOptionForm(self::formResponsable())
                    ->editOptionAction(function (Action $action) {
                        return $action->modalWidth(Width::ExtraSmall);
                    })*/
                    ->getOptionLabelFromRecordUsing(fn (Responsable $record): string => Str::upper(formatoMillares($record->cedula, 0).' '.$record->nombre))
                    ->searchable(['nombre', 'cedula'])
                    ->preload()
                    ->required(),
            ])
            ->compact();
    }

    protected static function sectionRubros()
    {
        return Section::make('Rubros')
            ->schema([
                Repeater::make(self::$repeatRelation)
                    ->label('Rubros')
                    ->hiddenLabel()
                    ->relationship()
                    ->schema(array_filter([
                        Select::make('rubros_id')
                            ->relationship(name: 'rubro', titleAttribute: 'nombre')
                            ->createOptionForm([
                                TextInput::make('nombre')
                                    ->label('Rubro')
                                    ->maxLength(255)
                                    ->unique()
                                    ->required(),
                                TextInput::make('peso_unitario')
                                    ->label('Peso Unitario')
                                    ->numeric()
                                    ->required(),
                                Select::make('unidad_medida')
                                    ->label('Unidad')
                                    ->options([
                                        'KG' => 'KG',
                                        'LT' => 'LT',
                                    ])
                                    ->required(),
                            ])
                            ->createOptionAction(callback: function (Action $action) {
                                return $action->modalWidth(Width::ExtraSmall);
                            })
                            ->getOptionLabelFromRecordUsing(fn (Rubro $record): string => Str::upper($record->nombre))
                            ->searchable()
                            ->preload()
                            ->distinct() // Asegura que el valor sea único en el contexto del Repeater
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems() // Deshabilita la opción en las otras filas
                            ->required()
                            ->live()
                            ->afterStateUpdated(callback: function (?string $state, ?string $old, Set $set) {
                                $rubro = Rubro::find($state);
                                if ($rubro) {
                                    $set('peso_unitario', $rubro->peso_unitario);
                                    $set('rubros_nombre', $rubro->nombre);
                                    $set('rubros_unidad_medida', $rubro->unidad_medida);
                                }
                            }),
                        self::$recepcion ? DatePicker::make('fecha_fabricacion') : self::selectTipoAdquisicion(),
                        self::$recepcion ? DatePicker::make('fecha_vencimiento') : null,
                        TextInput::make('cantidad_unidades')
                            ->label('Cantidad')
                            ->integer()
                            ->minValue(1)
                            ->required()
                            ->live(onBlur: true)
                            ->rules(self::rulesCantidad()),
                        TextInput::make('peso_unitario')
                            ->label('Peso Unitario')
                            ->numeric()
                            ->step(0.01)
                            ->required()
                            ->live(onBlur: true)
                            ->suffix(function (Get $get, Set $set): string {
                                $cantidad = $get('cantidad_unidades');
                                $peso = $get('peso_unitario');
                                $total = round($cantidad * $peso, 2);
                                $set('total', $total);
                                $unidad = $get('rubros_unidad_medida') ?? 'KG';

                                return 'Total: '.formatoMillares($total).' '.$unidad;
                            })
                            ->rules(self::rulesPeso()),
                        self::$recepcion ? self::selectTipoAdquisicion() : null,
                        Hidden::make('rubros_nombre'),
                        Hidden::make('rubros_unidad_medida'),
                        Hidden::make('total'),
                    ]))
                    ->minItems(1)
                    ->columns(fn (): int => self::$recepcion ? 3 : 2)
                    ->columnSpanFull(),
            ])
            ->compact()
            ->columns()
            ->columnSpanFull();
    }

    protected static function sectionObservacion()
    {
        return Section::make('Observación')
            ->schema([
                Textarea::make('observacion')
                    ->hiddenLabel()
                    ->required()
                    ->columnSpanFull(),
            ])
            ->compact()
            ->columnSpanFull();
    }

    protected static function formResponsable(): array
    {
        return [
            TextInput::make('cedula')
                ->label('Cédula')
                ->integer()
                ->unique()
                ->required(),
            TextInput::make('nombre')
                ->label('Nombre y Apellido')
                ->required(),
            TextInput::make('telefono')
                ->label('Teléfono')
                ->tel()
                ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/'),
            TextInput::make('empresa'),
        ];
    }

    protected static function getPlan($codigo): mixed
    {
        return Plan::where('codigo', $codigo)->first()?->id;
    }

    protected static function selectTipoAdquisicion()
    {
        return Select::make('tipo_adquisicion')
            ->label('Tipo adquisición')
            ->options([
                'asignacion' => 'ASIGNACIÓN',
                'propia' => 'PROPIA',
                'mercal' => 'MERCAL',
                'pdval' => 'PDVAL',
                'fundaproal' => 'FUNDAPROAL',
                'inn' => 'INN',
            ])
            ->live()
            ->required();
    }

    protected static function rulesCantidad(): array
    {
        return [
            fn (Get $get, ?Model $record): Closure => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                // Solo validar si no es una recepción
                if (! self::$recepcion) {
                    $rubroId = $get('rubros_id');
                    // Usamos '../' para salir del Repeater y buscar en el formulario padre
                    // Si el Repeater está dentro de una Section, podrías necesitar '../../'
                    $almacenId = $get('../../almacenes_id');
                    $planId = $get('../../planes_id');
                    $tipoAdquisicion = $get('tipo_adquisicion');

                    if (! $rubroId) {
                        return;
                    }

                    // Buscamos el registro de stock para ese rubro y almacén
                    $stock = Stock::where('rubros_id', $rubroId)
                        ->where('planes_id', $planId)
                        ->where('almacenes_id', $almacenId)
                        ->first();

                    $disponible = 0;
                    if ($stock) {
                        if ($tipoAdquisicion == 'asignacion') {
                            $disponible = $stock->asignacion_cantidad - $stock->despacho_asignacion_cantidad;
                        } else {
                            $disponible = $stock->propia_cantidad - $stock->despacho_propia_cantidad;
                        }
                    }

                    // --- LÓGICA DE EDICIÓN INTELIGENTE ---
                    if ($record) {
                        // Solo sumamos si el rubro y el tipo de adquisición NO han cambiado en el formulario.
                        // Si cambiaron, validamos contra el stock real del nuevo rubro/tipo.
                        if ($record->rubros_id == $rubroId && $record->tipo_adquisicion === $tipoAdquisicion) {
                            // IMPORTANTE: Usamos cantidad_unidades porque estamos en rulesCantidad
                            $disponible += (int) $record->cantidad_unidades;
                        }
                    }

                    if ($value > $disponible) {
                        $fail("Stock insuficiente. Hay {$disponible} unidades disponibles.");
                    }
                }
            },
        ];
    }

    protected static function rulesPeso(): array
    {
        return [
            fn (Get $get, ?Model $record): Closure => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                // Solo validar si no es una recepción
                if (! self::$recepcion) {
                    $rubroId = $get('rubros_id');
                    // Usamos '../' para salir del Repeater y buscar en el formulario padre
                    // Si el Repeater está dentro de una Section, podrías necesitar '../../'
                    $almacenId = $get('../../almacenes_id');
                    $planId = $get('../../planes_id');
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

                    // --- LÓGICA DE EDICIÓN INTELIGENTE ---
                    if ($record) {
                        // SOLO devolvemos el stock si el rubro Y el tipo son los mismos que ya estaban grabados.
                        // Si el usuario cambió el rubro en el Select, el disponible debe ser el stock real
                        // de ese nuevo rubro, sin sumarle lo del rubro anterior.
                        if ($record->rubros_id == $rubroId && $record->tipo_adquisicion === $tipoAdquisicion) {
                            $disponible += (float) $record->total; // o cantidad_unidades
                        }
                    }

                    if ($total > $disponible) {
                        $unidad = $stock?->rubro?->unidad_medida ?? '';
                        $disponibleFormateado = number_format($disponible, 2, ',', '.');

                        $fail("Stock insuficiente. Hay {$disponibleFormateado} {$unidad} disponibles.");
                    }
                }
            },
        ];
    }
}
