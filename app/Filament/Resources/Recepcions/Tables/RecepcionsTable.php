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
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class RecepcionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->orderByDesc('fecha')->orderByDesc('hora'))
            ->columns([
                TextColumn::make('recepcion')
                    ->label('Fecha')
                    ->default(fn(Recepcion $record): string => Carbon::parse($record->fecha)->translatedFormat('M d, Y'))
                    ->description(fn(Recepcion $record): string => $record->plan->nombre)
                    ->hiddenFrom('md')
                    ->icon(fn(Recepcion $record): Heroicon => match (self::getEstatus($record)) {
                        'is_complete' => Heroicon::OutlinedDocumentCheck,
                        'is_sealed' => Heroicon::OutlinedCheckBadge,
                        default => Heroicon::OutlinedClock
                    })
                    ->iconColor(fn(Recepcion $record): string => match (self::getEstatus($record)) {
                        'is_complete' => 'success',
                        'is_sealed' => 'info',
                        default => 'gray'
                    }),
                TextColumn::make('fecha')
                    ->date()
                    ->description(fn(Recepcion $record): string => Carbon::parse($record->hora)->translatedFormat('h:i a'))
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
                    ->description(fn(Recepcion $record): string => $record->responsables_telefono ?? '-')
                    ->formatStateUsing(fn(string $state): string => Str::upper($state))
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
                    ->default(fn(Recepcion $record): string => self::getEstatus($record))
                    ->icon(fn(string $state): Heroicon => match ($state) {
                        'is_complete' => Heroicon::OutlinedDocumentCheck,
                        'is_sealed' => Heroicon::OutlinedCheckBadge,
                        default => Heroicon::OutlinedClock
                    })
                    ->color(fn(string $state): string => match ($state) {
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
}
