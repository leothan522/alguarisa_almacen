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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class RecepcionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('recepcion')
                    ->label('Fecha')
                    ->default(fn(Recepcion $record): string => Carbon::parse($record->fecha)->translatedFormat('M d, Y'))
                    ->description(fn(Recepcion $record): string => formatoMillares($record->items()->sum('total')) . " KG")
                    ->hiddenFrom('md'),
                TextColumn::make('numero')
                    ->label('Número')
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('fecha')
                    ->date()
                    ->description(fn(Recepcion $record): string => Carbon::parse($record->hora)->translatedFormat('h:i a'))
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('plan.nombre'),
                TextColumn::make('responsables_nombre')
                    ->label('Entrega')
                    ->description(fn(Recepcion $record): string => $record->responsable->telefono ?? '-')
                    ->formatStateUsing(fn(string $state): string => Str::upper($state))
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('items_sum_total')
                    ->label('Total')
                    ->sum('items', 'total')
                    ->numeric()
                    ->suffix(' KG')
                    ->alignEnd()
                    ->visibleFrom('md'),
            ])
            ->filters([
                
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
}
