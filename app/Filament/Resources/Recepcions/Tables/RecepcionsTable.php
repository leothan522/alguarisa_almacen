<?php

namespace App\Filament\Resources\Recepcions\Tables;

use App\Models\Recepcion;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use pxlrbt\FilamentExcel\Actions\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class RecepcionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->orderByDesc('fecha')->orderByDesc('hora'))
            ->columns([
                TextColumn::make('recepcion')
                    ->label('Fecha')
                    ->default(fn (Recepcion $record): string => Carbon::parse($record->fecha)->translatedFormat('M d, Y'))
                    ->description(fn (Recepcion $record): string => $record->plan->nombre)
                    ->hiddenFrom('md')
                    ->icon(fn (Recepcion $record): Heroicon => match (self::getEstatus($record)) {
                        'is_complete' => Heroicon::OutlinedDocumentCheck,
                        'is_sealed' => Heroicon::OutlinedCheckBadge,
                        default => Heroicon::OutlinedClock
                    })
                    ->iconColor(fn (Recepcion $record): string => match (self::getEstatus($record)) {
                        'is_complete' => 'success',
                        'is_sealed' => 'info',
                        default => 'gray'
                    }),
                TextColumn::make('fecha')
                    ->date()
                    ->description(fn (Recepcion $record): string => Carbon::parse($record->hora)->translatedFormat('h:i a'))
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
                    ->visibleFrom('xl')
                    ->searchable(),
                TextColumn::make('responsables_nombre')
                    ->label('Entrega')
                    ->description(fn (Recepcion $record): string => $record->responsables_telefono ?? '-')
                    ->formatStateUsing(fn (string $state): string => Str::upper($state))
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('items_sum_cantidad_unidades')
                    ->label('Unidades')
                    ->sum('items', 'cantidad_unidades')
                    ->numeric()
                    ->suffix(' UND')
                    ->alignEnd()
                    ->visibleFrom('md'),
                TextColumn::make('total_movil')
                    ->label('Total')
                    ->default(fn (Recepcion $record) => $record->items()->sum('total'))
                    ->description(fn (Recepcion $record): string => formatoMillares($record->items()->sum('cantidad_unidades'), 0).' UND')
                    ->numeric(decimalPlaces: 2)
                    ->weight(FontWeight::Bold)
                    ->size(TextSize::Medium)
                    ->color('violet')
                    ->suffix(' KG')
                    ->alignEnd()
                    ->hiddenFrom('md'),
                TextColumn::make('items_sum_total')
                    ->label('Total')
                    ->sum('items', 'total')
                    ->numeric(decimalPlaces: 2)
                    ->weight(FontWeight::Bold)
                    ->size(TextSize::Medium)
                    ->color('violet')
                    ->suffix(' KG')
                    ->alignEnd()
                    ->visibleFrom('md'),
                IconColumn::make('estatus')
                    ->label('Estatus')
                    ->default(fn (Recepcion $record): string => self::getEstatus($record))
                    ->icon(fn (string $state): Heroicon => match ($state) {
                        'is_complete' => Heroicon::OutlinedDocumentCheck,
                        'is_sealed' => Heroicon::OutlinedCheckBadge,
                        default => Heroicon::OutlinedClock
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'is_complete' => 'success',
                        'is_sealed' => 'info',
                        default => 'gray'
                    })
                    ->alignCenter()
                    ->visibleFrom('md'),
            ])
            ->filters([
                self::filterMes(),
                SelectFilter::make('plan')
                    ->relationship('plan', 'nombre'),
                self::filterEstatus(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    self::actionExportPdf(),
                    ViewAction::make()
                        ->label('Ver Fotos'),
                    self::actionValidarRecepcion(),
                    self::actionSubirExpediente(),
                    self::actionVerExpediente(),
                    EditAction::make(),
                    self::actionRevertirRecepcion(),
                    self::actionRevertirExpediente(),
                    RestoreAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorizeIndividualRecords('delete'),
                    ForceDeleteBulkAction::make()
                        ->authorizeIndividualRecords('forceDelete'),
                    RestoreBulkAction::make()
                        ->authorizeIndividualRecords('restore'),
                ]),
                self::actionExportExcel(),
                Action::make('actualizar')
                    ->icon(Heroicon::ArrowPath)
                    ->iconButton(),
            ]);
    }

    public static function getEstatus(Recepcion $record): string
    {
        $validado = $record->is_sealed ?? false;
        $response = 'default';
        if ($validado) {
            if ($record->is_complete) {
                $response = 'is_complete';
            } else {
                $response = 'is_sealed';
            }
        }

        return $response;
    }

    protected static function actionExportPdf()
    {
        return Action::make('export-pdf')
            ->label('Imprimir')
            ->icon(Heroicon::OutlinedPrinter)
            ->url(fn (Recepcion $record): string => route('dashboard.export-pdf.recepcion', $record->id))
            ->openUrlInNewTab()
            ->visible(fn (Recepcion $record): bool => ! $record->deleted_at);
    }

    protected static function actionValidarRecepcion()
    {
        return Action::make('validar-recepcion')
            ->label('Validar Recepción')
            ->color('info')
            ->icon(Heroicon::OutlinedCheckBadge)
            ->schema([
                FileUpload::make('image_documento')
                    ->label('Foto Firmada y Sello')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('images-recepciones')
                    ->visibility('public')
                    ->required()
                    ->maxSize(2048)
                    // --- AJUSTE PARA FORMATO CARTA / VERTICAL ---
                    // Mantenemos 1200 de ancho pero damos más margen al alto
                    ->automaticallyResizeImagesToWidth('1200')
                    ->automaticallyResizeImagesToHeight('1600')
                    ->automaticallyResizeImagesMode('inset'), // 'inset' asegura que la imagen quepa sin recortarse
                FileUpload::make('image_1')
                    ->label('Memoria Fotográfica')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('images-recepciones')
                    ->visibility('public')
                    ->required()
                    ->maxSize(2048)
                    ->automaticallyResizeImagesToWidth('1200')
                    ->automaticallyResizeImagesToHeight('1200')
                    ->automaticallyResizeImagesMode('inset'),
                FileUpload::make('image_2')
                    ->label('Memoria Fotográfica ')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('images-recepciones')
                    ->visibility('public')
                    ->maxSize(2048)
                    ->automaticallyResizeImagesToWidth('1200')
                    ->automaticallyResizeImagesToHeight('1200')
                    ->automaticallyResizeImagesMode('inset'),
            ])
            ->action(function (array $data, Recepcion $record): void {
                $record->update([
                    'image_documento' => $data['image_documento'],
                    'image_1' => $data['image_1'],
                    'image_2' => $data['image_2'] ?? null,
                    'is_sealed' => 1,
                ]);
                Notification::make()
                    ->title('Datos Guardados')
                    ->send();
            })
            ->modalWidth(Width::Small)
            ->visible(fn ($record) => ! $record->is_sealed && ! $record->deleted_at && self::isVisible());
    }

    protected static function borrarFotos($fotoPath): void
    {
        if ($fotoPath && Storage::disk('public')->exists($fotoPath)) {
            Storage::disk('public')->delete($fotoPath);
        }
    }

    protected static function actionRevertirRecepcion()
    {
        return Action::make('revertir-recepcion')
            ->label('Revertir Validación')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('¿Revertir esta recepción?')
            ->modalDescription('Se eliminarán las fotos del servidor y la recepción volverá a estar pendiente.')
            ->visible(fn (Recepcion $record): bool => $record->is_sealed && ! $record->is_complete && self::isVisible())
            ->action(function (Recepcion $record) {
                $fotoDocumento = $record->image_documento;
                $fotoImage1 = $record->image_1;
                $fotoImage2 = $record->image_2;
                self::borrarFotos($fotoDocumento);
                self::borrarFotos($fotoImage1);
                self::borrarFotos($fotoImage2);
                $record->update([
                    'is_sealed' => false,
                    'image_documento' => null,
                    'image_1' => null,
                    'image_2' => null,
                ]);
                Notification::make()
                    ->title('Recepción revertida')
                    ->body('Las fotos fueron eliminadas y el estado se ha restablecido.')
                    ->warning()
                    ->send();
            });
    }

    protected static function actionSubirExpediente()
    {
        return Action::make('subir-expediente')
            ->label('Subir Expediente')
            ->icon(Heroicon::OutlinedDocumentArrowUp)
            ->color('success')
            ->visible(fn (Recepcion $record): bool => $record->is_sealed && ! $record->is_complete && self::isVisible())
            ->schema([
                FileUpload::make('pdf_expediente')
                    ->label('Expediente Escaneado (PDF)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(20480) // 20M
                    ->disk('public')
                    ->directory('pdf-recepciones')
                    ->visibility('public')
                    ->required()
                    ->helperText('Asegúrate de que todos los documentos estén en un solo PDF.')
                    ->getUploadedFileNameForStorageUsing(function (Recepcion $record, $file): string {
                        $prefix = Str::slug("Expediente-{$record->numero}-Recepcion");

                        return (string) \str($prefix.'.'.$file->getClientOriginalExtension());
                    }),
            ])
            ->action(function (array $data, Recepcion $record) {
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

    protected static function actionRevertirExpediente()
    {
        return Action::make('revertir-expediente')
            ->label('Revertir Expediente')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('¿Eliminar el expediente cargado?')
            ->modalDescription('El archivo PDF se borrará permanentemente del servidor y podrás subir uno nuevo.')
            ->visible(fn (Recepcion $record): bool => $record->is_complete && self::isVisible())
            ->action(function (Recepcion $record) {
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

    protected static function actionVerExpediente()
    {
        return Action::make('abrir-expediente')
            ->label('Ver Expediente')
            ->icon(Heroicon::OutlinedDocumentCheck)
            ->color('gray')
            ->visible(fn (Recepcion $record): bool => $record->is_complete)
            ->mountUsing(function (Recepcion $record, Action $action) {
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
            ->modalContent(function (Recepcion $record): HtmlString {
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
            })/*->modalContent(fn(Recepcion $record) => new HtmlString('
                <div style="height: 75vh;">
                    <iframe
                        src="' . Storage::url($record->pdf_expediente) . '"
                        width="100%"
                        height="100%"
                        style="border: none; border-radius: 8px;">
                    </iframe>
                </div>
            '))*/ ;
    }

    protected static function filterEstatus()
    {
        return SelectFilter::make('estatus')
            ->label('Estatus')
            ->options([
                'pendiente' => 'Pendientes',
                'validado' => 'Validadas',
                'completa' => 'Completas',
            ])
            ->query(function (Builder $query, array $data): Builder {
                return $query->when(
                    $data['value'],
                    function (Builder $query, $value) {
                        return match ($value) {
                            'pendiente' => $query->where('is_sealed', false)
                                ->where('is_complete', false),
                            'validado' => $query->where('is_sealed', true),
                            'completa' => $query->where('is_complete', true),
                            default => $query,
                        };
                    }
                );
            });
    }

    protected static function filterMes()
    {
        return Filter::make('fecha')
            ->schema([
                DatePicker::make('mes')
                    ->label('Seleccionar Mes')
                    ->format('Y-m'),
            ])
            ->query(function (Builder $query, array $data): Builder {
                return $query->when(
                    $data['mes'],
                    function (Builder $query, $mes) {
                        $fecha = Carbon::parse($mes);

                        return $query
                            ->whereMonth('fecha', $fecha->month)
                            ->whereYear('fecha', $fecha->year);
                    }
                );
            })
            ->indicateUsing(function (array $data): ?string {
                if (! $data['mes']) {
                    return null;
                }

                return 'Mes '.Carbon::parse($data['mes'])->translatedFormat('F Y');
            });
    }

    protected static function isVisible(): bool
    {
        return isAdmin() || auth()->user()->hasRole('almacen');
    }

    protected static function actionExportExcel()
    {
        return ExportBulkAction::make()->exports([
            ExcelExport::make()
                ->withColumns([
                    Column::make('fecha')
                        ->heading('FECHA')
                        ->formatStateUsing(fn ($state) => Carbon::parse($state)->format('d/m/Y'))
                        ->format(NumberFormat::FORMAT_DATE_DDMMYYYY),
                    Column::make('hora')
                        ->heading('Hora')
                        ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('h:i a'))
                        ->format(NumberFormat::FORMAT_DATE_TIME1),
                    Column::make('numero')
                        ->heading('NÚMERO')
                        // ->formatStateUsing(fn($state) => '="' . $state . '"')
                        ->format(NumberFormat::FORMAT_TEXT),
                    Column::make('plan.nombre')
                        ->heading('PLAN'),
                    Column::make('responsables_nombre')
                        ->heading('ENTREGA')
                        ->formatStateUsing(fn ($state) => Str::upper($state)),
                    Column::make('responsables_cedula')
                        ->heading('CÉDULA')
                        ->format(NumberFormat::FORMAT_NUMBER),
                    Column::make('responsables_telefono')
                        ->heading('TELÉFONO')
                        ->format(NumberFormat::FORMAT_TEXT),
                    Column::make('is_sealed')
                        ->heading('ESTATUS')
                        ->formatStateUsing(fn (Recepcion $record) => match (self::getEstatus($record)) {
                            'is_sealed' => 'VALIDADA',
                            'is_complete' => 'COMPLETA',
                            default => 'PENDIENTE'
                        }),
                    Column::make('total_unidades')
                        ->heading('UNIDADES')
                        ->format(NumberFormat::FORMAT_NUMBER),
                    Column::make('total_peso')
                        ->heading('PESO TOTAL (KG)')
                        ->format(NumberFormat::FORMAT_NUMBER_00),
                ])
                ->modifyQueryUsing(fn (Builder $query) => $query->with('items')->orderBy('fecha')),
        ]);
    }
}
