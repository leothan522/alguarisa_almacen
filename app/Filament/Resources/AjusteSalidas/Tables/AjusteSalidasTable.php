<?php

namespace App\Filament\Resources\AjusteSalidas\Tables;

use App\Filament\Resources\BodegaMovils\Tables\BodegaMovilsTable;
use App\Filament\Resources\Recepcions\Tables\RecepcionsTable;
use App\Models\Despacho;
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
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class AjusteSalidasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('is_adjustment', true)->orderByDesc('fecha')->orderByDesc('hora'))
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
                        'is_sealed' => Heroicon::OutlinedCheckBadge,
                        'is_complete' => Heroicon::OutlinedDocumentCheck,
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
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    BodegaMovilsTable::actionExportPdf(),
                    ViewAction::make()
                        ->label('Ver Fotos'),
                    self::actionValidarSalida(),
                    self::actionSubirExpediente(),
                    BodegaMovilsTable::actionVerExpediente(),
                    self::actionRevertirValidacion(),
                    BodegaMovilsTable::actionRevertirExpediente(),
                    EditAction::make(),
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

    protected static function actionValidarSalida()
    {
        return Action::make('subir-fotos')
            ->label('Validar Salida')
            ->color('info')
            ->icon(Heroicon::OutlinedCheckBadge)
            ->schema([
                FileUpload::make('image_documento')
                    ->label('Foto Firmada y Sello')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('images-despachos')
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
                    ->directory('images-despachos')
                    ->visibility('public')
                    ->required()
                    ->maxSize(2048)
                    ->automaticallyResizeImagesToWidth('1200')
                    ->automaticallyResizeImagesToHeight('1200')
                    ->automaticallyResizeImagesMode('inset'),
                FileUpload::make('image_2')
                    ->label('Memoria Fotográfica')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('images-despachos')
                    ->visibility('public')
                    ->maxSize(2048)
                    ->automaticallyResizeImagesToWidth('1200')
                    ->automaticallyResizeImagesToHeight('1200')
                    ->automaticallyResizeImagesMode('inset'),
            ])
            ->action(function (array $data, Despacho $record): void {
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
            ->visible(fn ($record) => ! $record->is_sealed && ! $record->deleted_at && RecepcionsTable::isVisible());
    }

    public static function actionSubirExpediente()
    {
        return Action::make('subir-expediente')
            ->label('Subir Expediente')
            ->icon(Heroicon::OutlinedDocumentArrowUp)
            ->color('success')
            ->visible(fn (Despacho $record): bool => ! $record->deleted_at && $record->is_sealed && ! $record->is_complete && BodegaMovilsTable::isVisible())
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

    protected static function actionRevertirValidacion()
    {
        return Action::make('revertir-validacion')
            ->label('Revertir Validación')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('¿Revertir este ajuste?')
            ->modalDescription('Se eliminarán las fotos del servidor y el ajuste volverá a estar pendiente.')
            ->visible(fn (Despacho $record): bool => $record->is_sealed && ! $record->is_complete && BodegaMovilsTable::isVisible())
            ->action(function (Despacho $record) {
                $fotoDocumento = $record->image_documento;
                $fotoImage1 = $record->image_1;
                $fotoImage2 = $record->image_2;
                RecepcionsTable::borrarFotos($fotoDocumento);
                RecepcionsTable::borrarFotos($fotoImage1);
                RecepcionsTable::borrarFotos($fotoImage2);
                $record->update([
                    'is_sealed' => false,
                    'image_documento' => null,
                    'image_1' => null,
                    'image_2' => null,
                ]);
                Notification::make()
                    ->title('Validación revertida')
                    ->body('Las fotos fueron eliminadas y el estado se ha restablecido.')
                    ->warning()
                    ->send();
            });
    }


}
