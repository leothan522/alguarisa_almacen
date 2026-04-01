<?php

namespace App\Filament\Resources\AjusteEntradas\Tables;

use App\Filament\Resources\Recepcions\Tables\RecepcionsTable;
use App\Models\Recepcion;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
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

class AjusteEntradasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('is_adjustment', true)->orderByDesc('fecha')->orderByDesc('hora'))
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
                    ->visibleFrom('2xl')
                    ->searchable(),
                TextColumn::make('responsables_nombre')
                    ->label('Entrega')
                    ->wrap()
                    ->description(fn (Recepcion $record): string => $record->responsables_telefono ?? '-')
                    ->formatStateUsing(fn (string $state): string => Str::upper($state))
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('items_sum_cantidad_unidades')
                    ->label('Und. Totales')
                    ->sum('items', 'cantidad_unidades')
                    ->numeric()
                    ->suffix(' UND')
                    ->alignEnd()
                    ->visibleFrom('md'),
                TextColumn::make('total_movil')
                    ->label('Peso Total')
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
                    ->label('Peso Total')
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
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    RecepcionsTable::actionExportPdf(),
                    self::actionSubirExpediente(),
                    RecepcionsTable::actionVerExpediente(),
                    EditAction::make(),
                    RecepcionsTable::actionRevertirExpediente(),
                    RestoreAction::make()
                        ->before(function (Recepcion $record) {
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
                RecepcionsTable::actionExportExcel(),
                Action::make('actualizar')
                    ->icon(Heroicon::ArrowPath)
                    ->iconButton(),
            ]);
    }

    protected static function getEstatus(Recepcion $record): string
    {
        $validado = $record->is_complete ?? false;
        $response = 'default';
        if ($validado) {
            $response = 'is_complete';
        }

        return $response;
    }

    protected static function actionSubirExpediente()
    {
        return Action::make('subir-expediente')
            ->label('Subir Expediente')
            ->icon(Heroicon::OutlinedDocumentArrowUp)
            ->color('success')
            ->visible(fn (Recepcion $record): bool => ! $record->deleted_at && ! $record->is_complete && RecepcionsTable::isVisible())
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
}
