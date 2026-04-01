<?php

namespace App\Filament\Resources\BodegaMovils\Tables;

use App\Models\Despacho;
use App\Models\Detalle;
use App\Models\Rubro;
use App\Traits\AlmacenSchemas;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class BodegaMovilsTable
{
    use AlmacenSchemas;

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->whereRelation('plan', 'codigo', 'BM')->where('is_return', false)->where('is_adjustment', false)->orderByDesc('fecha')->orderByDesc('hora'))
            ->columns([
                TextColumn::make('recepcion')
                    ->label('Fecha')
                    ->default(fn (Despacho $record): string => Carbon::parse($record->fecha)->translatedFormat('M d, Y'))
                    ->description(fn (Despacho $record): string => $record->plan->nombre)
                    ->hiddenFrom('md')
                    ->icon(fn (Despacho $record): Heroicon => match (self::getEstatus($record)) {
                        'is_complete' => Heroicon::OutlinedDocumentCheck,
                        'is_return' => Heroicon::OutlinedInboxArrowDown,
                        default => Heroicon::OutlinedClock
                    })
                    ->iconColor(fn (Despacho $record): string => match (self::getEstatus($record)) {
                        'is_complete' => 'success',
                        'is_return' => 'info',
                        default => 'gray'
                    }),
                TextColumn::make('fecha')
                    ->date()
                    ->description(fn (Despacho $record): string => Carbon::parse($record->hora)->translatedFormat('h:i a'))
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('numero')
                    ->label('Número')
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('plan.nombre')
                    ->wrap()
                    ->visibleFrom('md'),
                TextColumn::make('responsables_cedula')
                    ->label('Cédula')
                    ->numeric()
                    ->visibleFrom('2xl')
                    ->searchable(),
                TextColumn::make('responsables_nombre')
                    ->label('Recibe')
                    ->wrap()
                    ->description(fn (Despacho $record): string => $record->responsables_telefono ?? '-')
                    ->formatStateUsing(fn (string $state): string => Str::upper($state))
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('detalles_sum_cantidad_unidades')
                    ->label('Und. Totales')
                    ->sum('detalles', 'cantidad_unidades')
                    ->formatStateUsing(function ($state) {
                        // Si el estado es 0, nulo o vacío, retornamos el texto especial
                        if (! $state || $state == 0) {
                            return 'MERMA';
                        }

                        // Si tiene valor, lo formateamos manualmente (ya que formatStateUsing anula el ->numeric())
                        return formatoMillares($state, 0).' UND';
                    })
                    ->badge(fn (Despacho $record): bool => $record->is_merma)
                    ->alignEnd()
                    ->visibleFrom('md'),
                TextColumn::make('total_movil')
                    ->label('Peso Total')
                    ->default(fn (Despacho $record) => $record->detalles()->sum('total'))
                    ->description(fn (Despacho $record): string => $record->is_merma ? 'MERMA' : formatoMillares($record->detalles()->sum('cantidad_unidades'), 0).' UND')
                    ->numeric(decimalPlaces: 2)
                    ->weight(FontWeight::Bold)
                    ->size(TextSize::Medium)
                    ->color('violet')
                    ->suffix(' KG')
                    ->alignEnd()
                    ->hiddenFrom('md'),
                TextColumn::make('detalles_sum_total')
                    ->label('Peso Total')
                    ->sum('detalles', 'total')
                    ->numeric(decimalPlaces: 2)
                    ->weight(FontWeight::Bold)
                    ->size(TextSize::Medium)
                    ->color('violet')
                    ->suffix(' KG')
                    ->alignEnd()
                    ->visibleFrom('md'),
                IconColumn::make('estatus')
                    ->label('Estatus')
                    ->default(fn (Despacho $record): string => self::getEstatus($record))
                    ->icon(fn (string $state): Heroicon => match ($state) {
                        'is_complete' => Heroicon::OutlinedDocumentCheck,
                        'is_return' => Heroicon::OutlinedInboxArrowDown,
                        default => Heroicon::OutlinedClock
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'is_complete' => 'success',
                        'is_return' => 'info',
                        default => 'gray'
                    })
                    ->alignCenter()
                    ->visibleFrom('md'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    self::actionExportPdf(),
                    self::actionImprimirDevolucion(),
                    self::actionImprimirNotaVenta(),
                    self::actionCargarDevolucion(),
                    self::actionSubirExpediente(),
                    self::actionVerExpediente(),
                    self::actionRevertirExpediente(),
                    self::actionRevertirDevolucion(),
                    EditAction::make(),
                    DeleteAction::make()
                        ->before(function (Despacho $record) {
                            $numero = '*'.$record->numero;
                            $record->update([
                                'numero' => $numero,
                            ]);
                        })
                        ->visible(fn (Despacho $record): bool => $record->is_merma),
                    RestoreAction::make()
                        ->before(function (Despacho $record) {
                            $numero = Str::replace('*', '', $record->numero);
                            $record->update([
                                'numero' => $numero,
                            ]);
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ForceDeleteBulkAction::make()
                        ->authorizeIndividualRecords('forceDelete'),
                    RestoreBulkAction::make()
                        ->authorizeIndividualRecords('restore'),
                ]),
                Action::make('actualizar')
                    ->icon(Heroicon::ArrowPath)
                    ->iconButton(),
            ]);
    }

    public static function getEstatus(Despacho $record): string
    {
        $response = 'default';

        if (self::existeDevolucion($record)) {
            $response = 'is_return';
        }

        if ($record->is_complete) {
            $response = 'is_complete';
        }

        return $response;
    }

    public static function actionExportPdf()
    {
        return Action::make('export-pdf')
            ->label('Imprimir')
            ->icon(Heroicon::OutlinedPrinter)
            ->url(fn (Despacho $record): string => route('dashboard.export-pdf.despacho', $record->id))
            ->openUrlInNewTab()
            ->visible(fn (Despacho $record): bool => ! $record->deleted_at);
    }

    public static function actionSubirExpediente()
    {
        return Action::make('subir-expediente')
            ->label('Subir Expediente')
            ->icon(Heroicon::OutlinedDocumentArrowUp)
            ->color('success')
            ->visible(fn (Despacho $record): bool => ! $record->deleted_at && ! $record->is_complete && self::isVisible())
            ->schema([
                FileUpload::make('pdf_expediente')
                    ->label('Expediente Escaneado (PDF)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(20480) // 20M
                    ->disk('public')
                    ->directory('pdf-despachos-bm')
                    ->visibility('public')
                    ->required()
                    ->helperText('Asegúrate de que todos los documentos estén en un solo PDF.')
                    ->getUploadedFileNameForStorageUsing(function (Despacho $record, $file): string {
                        $prefix = Str::slug("Expediente-{$record->numero}-Despacho");

                        return (string) \str($prefix.'.'.$file->getClientOriginalExtension());
                    }),
            ])
            ->action(function (array $data, Despacho $record) {
                $record->update([
                    'pdf_expediente' => $data['pdf_expediente'],
                    'is_complete' => 1,
                ]);
                Notification::make()
                    ->title('Expediente cargado con éxito')
                    ->send();
            })
            ->modalWidth(Width::Small);
    }

    public static function actionRevertirExpediente()
    {
        return Action::make('revertir-expediente')
            ->label('Revertir Expediente')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('¿Eliminar el expediente cargado?')
            ->modalDescription('El archivo PDF se borrará permanentemente del servidor y podrás subir uno nuevo.')
            ->visible(fn (Despacho $record): bool => $record->is_complete && self::isVisible())
            ->action(function (Despacho $record) {
                $pdfPath = $record->pdf_expediente;
                self::borrarFotos($pdfPath);
                $record->update([
                    'pdf_expediente' => null,
                    'is_complete' => false,
                ]);
                Notification::make()
                    ->title('Expediente eliminado')
                    ->body('Ahora puedes subir el archivo correcto.')
                    ->warning()
                    ->send();
            });
    }

    public static function isVisible(): bool
    {
        return isAdmin() || auth()->user()->hasRole('almacen');
    }

    public static function borrarFotos($fotoPath): void
    {
        if ($fotoPath && Storage::disk('public')->exists($fotoPath)) {
            Storage::disk('public')->delete($fotoPath);
        }
    }

    public static function actionVerExpediente()
    {
        return Action::make('abrir-expediente')
            ->label('Ver Expediente')
            ->icon(Heroicon::OutlinedDocumentCheck)
            ->color('gray')
            ->visible(fn (Despacho $record): bool => $record->is_complete)
            ->mountUsing(function (Despacho $record, Action $action) {
                if (! Storage::disk('public')->exists($record->pdf_expediente)) {
                    Notification::make()
                        ->title('Archivo no encontrado')
                        ->body('El PDF del expediente no existe físicamente en el servidor.')
                        ->danger()
                        ->send();
                    $action->halt();
                }
            })
            ->modalWidth(Width::FiveExtraLarge)
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Cerrar')
            ->modalContent(function (Despacho $record): HtmlString {
                $pdfUrl = Storage::disk('public')->url($record->pdf_expediente);
                $viewerPath = asset('lib/pdfjs-legacy/web/viewer.html');
                $fullUrl = "{$viewerPath}?file=".urlencode($pdfUrl).'#view=FitW';

                return new HtmlString("
                    <div style='height: 60vh; width: 100%; overflow: hidden;'>
                    <iframe
                        src='{$fullUrl}'
                        width='100%'
                        height='100%'
                        style='border: none; border-radius: 8px;'
                        allow='fullscreen'>
                    </iframe>
                </div>
                ");
            });
    }

    protected static function actionCargarDevolucion()
    {
        return Action::make('cargar-devolucion')
            ->label('Registrar Devolución')
            ->icon(Heroicon::OutlinedInboxArrowDown)
            ->color('info')
            ->modalHeading('Registrar Devolución')
            ->schema([
                Grid::make()
                    ->schema([
                        DatePicker::make('fecha')
                            ->default(now())
                            ->required(),
                        TimePicker::make('hora')
                            ->default(now())
                            ->seconds(false)
                            ->required(),
                    ]),
                Repeater::make('detalles_devolucion')
                    ->label('Rubros')
                    ->schema([
                        Select::make('rubros_id')
                            ->label('Rubro')
                            ->options(fn (Despacho $record) => $record->detalles->pluck('rubros_nombre', 'rubros_id')->map(fn ($rubros_nombre) => Str::upper($rubros_nombre)))
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
                        self::selectTipoAdquisicion(),
                        TextInput::make('cantidad_unidades')
                            ->label('Cantidad')
                            ->integer()
                            ->minValue(1)
                            ->required()
                            ->live(onBlur: true)
                            ->rules(fn (Get $get, Despacho $record): array => [
                                function ($attribute, $value, $fail) use ($get, $record) {
                                    $rubroId = $get('rubros_id');
                                    $tipoAdquisicion = $get('tipo_adquisicion');
                                    if (! $rubroId) {
                                        return;
                                    }

                                    // Buscamos cuánto salió originalmente de ese rubro
                                    $original = $record->detalles->where('rubros_id', $rubroId)->where('tipo_adquisicion', $tipoAdquisicion)->sum('cantidad_unidades');

                                    // Buscamos cuánto se ha devuelto ya en otras devoluciones previas (si existen)
                                    $yaDevuelto = Detalle::whereHas('despacho', function ($q) use ($record) {
                                        $q->where('parent_id', $record->id)->where('is_return', true);
                                    })
                                        ->where('rubros_id', $rubroId)
                                        ->where('tipo_adquisicion', $tipoAdquisicion)
                                        ->sum('cantidad_unidades');

                                    $disponibleParaDevolver = $original - $yaDevuelto;

                                    if ($value > $disponibleParaDevolver) {
                                        $label = formatoMillares($disponibleParaDevolver, 0);
                                        $fail("La cantidad máxima para devolver es: {$label}");
                                    }
                                },
                            ]),
                        TextInput::make('peso_unitario')
                            ->label('Peso Unitario')
                            ->numeric()
                            ->step(0.01)
                            ->required()
                            ->live(onBlur: true)
                            ->suffix(function (Get $get, Set $set): string {
                                $cantidad = $get('cantidad_unidades');
                                $peso = $get('peso_unitario');
                                $total = $cantidad * $peso;
                                $set('total', $total);
                                $unidad = $get('rubros_unidad_medida') ?? 'KG';

                                return 'Total: '.formatoMillares($total).' '.$unidad;
                            })
                            ->rules(fn (Get $get, Despacho $record): array => [
                                function ($attribute, $value, $fail) use ($get, $record) {
                                    $rubroId = $get('rubros_id');
                                    $total = $get('total');
                                    $tipoAdquisicion = $get('tipo_adquisicion');
                                    if (! $rubroId) {
                                        return;
                                    }

                                    // Buscamos cuánto salió originalmente de ese rubro
                                    $original = $record->detalles->where('rubros_id', $rubroId)->where('tipo_adquisicion', $tipoAdquisicion)->sum('total');

                                    // Buscamos cuánto se ha devuelto ya en otras devoluciones previas (si existen)
                                    $yaDevuelto = Detalle::whereHas('despacho', function ($q) use ($record) {
                                        $q->where('parent_id', $record->id)->where('is_return', true);
                                    })
                                        ->where('rubros_id', $rubroId)
                                        ->where('tipo_adquisicion', $tipoAdquisicion)
                                        ->sum('total');

                                    $disponibleParaDevolver = $original - $yaDevuelto;

                                    if ($total > $disponibleParaDevolver) {
                                        $label = formatoMillares($disponibleParaDevolver);
                                        $fail("El peso máximo para devolver es: {$label}");
                                    }
                                },
                            ]),
                        Hidden::make('rubros_nombre'),
                        Hidden::make('rubros_unidad_medida'),
                        Hidden::make('total'),
                    ])
                    ->minItems(1)
                    ->compact()
                    ->columns()
                    ->reorderable(false),
                Textarea::make('observacion')
                    ->label('Observación')
                    ->required(),
            ])
            ->action(function (array $data, Despacho $record) {
                $devolucion = new Despacho;
                $devolucion->parent_id = $record->id;
                $devolucion->is_return = true;
                $devolucion->numero = 'DEV-'.$record->numero;
                $devolucion->fecha = $data['fecha'];
                $devolucion->hora = $data['hora'];
                $devolucion->observacion = $data['observacion'];
                $devolucion->almacenes_id = $record->almacenes_id;
                $devolucion->planes_id = $record->planes_id;
                $devolucion->jefes_id = $record->jefes_id;
                $devolucion->jefes_nombre = $record->jefes_nombre;
                $devolucion->jefes_cedula = $record->jefes_cedula;
                $devolucion->responsables_id = $record->responsables_id;
                $devolucion->responsables_nombre = $record->responsables_nombre;
                $devolucion->responsables_cedula = $record->responsables_cedula;
                $devolucion->responsables_telefono = $record->responsables_telefono;
                $devolucion->responsables_empresa = $record->responsables_empresa;
                $devolucion->save();

                foreach ($data['detalles_devolucion'] as $detalle) {
                    Detalle::create([
                        'despachos_id' => $devolucion->id,
                        'rubros_id' => $detalle['rubros_id'],
                        'rubros_nombre' => $detalle['rubros_nombre'],
                        'rubros_unidad_medida' => $detalle['rubros_unidad_medida'],
                        'cantidad_unidades' => $detalle['cantidad_unidades'],
                        'peso_unitario' => $detalle['peso_unitario'],
                        'total' => $detalle['total'],
                        'tipo_adquisicion' => $detalle['tipo_adquisicion'],
                    ]);
                }

                $devolucion->sincronizarStock();

                Notification::make()
                    ->title('Devolución procesada')
                    ->success()
                    ->send();

            })
            ->visible(fn() => self::isVisible())
            ->hidden(fn (Despacho $record): bool => self::existeDevolucion($record) || $record->is_complete || $record->is_merma);
    }

    protected static function existeDevolucion(Despacho $record): bool
    {
        return $record->devoluciones()->exists();
    }

    protected static function actionImprimirDevolucion()
    {
        return Action::make('imprimir-devolucion')
            ->label('Devolución')
            ->icon(Heroicon::OutlinedPrinter)
            ->url(function (Despacho $record) {
                $devolucion = Despacho::where('parent_id', $record->id)->first();
                if ($devolucion) {
                    return route('dashboard.export-pdf.despacho', $devolucion->id);
                }

                return null;
            })
            ->openUrlInNewTab()
            ->visible(fn (Despacho $record): bool => self::existeDevolucion($record));
    }

    protected static function actionImprimirNotaVenta()
    {
        return Action::make('nota-venta')
            ->label('Nota de Venta')
            ->icon(Heroicon::OutlinedPrinter)
            ->url(fn (Despacho $record) => route('dashboard.export-pdf.nota-venta', $record->id))
            ->openUrlInNewTab()
            ->hidden(fn (Despacho $record): bool => $record->is_merma);
    }

    protected static function actionRevertirDevolucion()
    {
        return Action::make('eliminar-devolucion')
            ->label('Revertir Devolución')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('¿Eliminar la devolución cargada?')
            ->visible(fn (Despacho $record): bool => ! $record->is_complete && self::existeDevolucion($record) && self::isVisible())
            ->action(function (Despacho $record) {
                $devolucion = Despacho::where('parent_id', $record->id)->first();
                if ($devolucion) {
                    $devolucion->numero = '*'.$devolucion->numero;
                    $devolucion->save();
                    $devolucion->delete();
                }
                Notification::make()
                    ->title('Devolución eliminada')
                    ->warning()
                    ->send();
            });
    }
}
