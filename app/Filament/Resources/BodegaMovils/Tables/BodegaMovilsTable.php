<?php

namespace App\Filament\Resources\BodegaMovils\Tables;

use App\Models\Despacho;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BodegaMovilsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('recepcion')
                    ->label('Fecha')
                    ->default(fn (Despacho $record): string => Carbon::parse($record->fecha)->translatedFormat('M d, Y'))
                    ->description(fn (Despacho $record): string => $record->plan->nombre)
                    ->hiddenFrom('md')
                    ->icon(fn (Despacho $record): Heroicon => match (self::getEstatus($record)) {
                        'is_complete' => Heroicon::OutlinedDocumentCheck,
                        'is_return' => Heroicon::OutlinedCheckBadge,
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
                    ->label('Unidades')
                    ->sum('detalles', 'cantidad_unidades')
                    ->numeric()
                    ->suffix(' UND')
                    ->alignEnd()
                    ->visibleFrom('md'),
                TextColumn::make('total_movil')
                    ->label('Peso Total')
                    ->default(fn (Despacho $record) => $record->detalles()->sum('total'))
                    ->description(fn (Despacho $record): string => formatoMillares($record->detalles()->sum('cantidad_unidades'), 0).' UND')
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
                        'is_return' => Heroicon::OutlinedCheckBadge,
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
        $validado = $record->is_return ?? false;
        $response = 'default';
        if ($validado) {
            if ($record->is_complete) {
                $response = 'is_complete';
            } else {
                $response = 'is_return';
            }
        }

        return $response;
    }

}
