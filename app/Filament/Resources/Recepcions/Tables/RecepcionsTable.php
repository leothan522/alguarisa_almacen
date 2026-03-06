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
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
                TextColumn::make('responsables_nombre')
                    ->label('Entrega')
                    ->description(fn (Recepcion $record): string => $record->responsables_telefono ?? '-')
                    ->formatStateUsing(fn (string $state): string => Str::upper($state))
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('items_sum_total')
                    ->label('Total')
                    ->sum('items', 'total')
                    ->numeric()
                    ->suffix(' KG')
                    ->alignEnd(),
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
                SelectFilter::make('plan')
                    ->relationship('plan', 'nombre'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    self::actionExportPdf(),
                    self::actionValidarRecepcion(),
                    self::actionRevertirRecepcion(),
                    self::actionSubirExpediente(),
                    self::revertirExpediente(),
                    EditAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
                Action::make('actualizar')
                    ->icon(Heroicon::ArrowPath)
                    ->iconButton(),
            ]);
    }

    protected static function getEstatus(Recepcion $record): string
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
            ->openUrlInNewTab();
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
                    ->maxSize(15360)
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
                    ->maxSize(15360)
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
                    ->maxSize(15360)
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
            ->visible(fn ($record) => ! $record->is_sealed);
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
            ->visible(fn (Recepcion $record): bool => $record->is_sealed && ! $record->is_complete)
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
                    ->success()
                    ->send();
            });
    }

    protected static function actionSubirExpediente()
    {
        return Action::make('subir-expediente')
            ->label('Subir Expediente PDF')
            ->icon(Heroicon::OutlinedDocumentArrowUp)
            ->color('success')
            ->visible(fn (Recepcion $record): bool => $record->is_sealed && ! $record->is_complete)
            ->schema([
                FileUpload::make('pdf_expediente')
                    ->label('Expediente Escaneado (PDF)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(20480) // 20M
                    ->disk('public')
                    ->directory('pdf-recepciones')
                    ->visibility('public')
                    ->required()
                    ->helperText('Asegúrate de que todos los documentos estén en un solo PDF.'),
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

    protected static function revertirExpediente()
    {
        return Action::make('revertir-expediente')
            ->label('Revertir Expediente')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('¿Eliminar el expediente cargado?')
            ->modalDescription('El archivo PDF se borrará permanentemente del servidor y podrás subir uno nuevo.')
            ->visible(fn (Recepcion $record): bool => $record->is_complete)
            ->action(function (Recepcion $record) {
                $pdfPath = $record->pdf_expediente;
                self::borrarFotos($pdfPath);
                $record->update([
                    'pdf_expediente' => null,
                    'is_complete' => false
                ]);
                Notification::make()
                    ->title('Expediente eliminado')
                    ->body('Ahora puedes subir el archivo correcto.')
                    ->warning()
                    ->send();
            });
    }
}
