<?php

namespace App\Filament\Resources\Recepcions\Tables;

use App\Models\Recepcion;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class RecepcionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->searchable(),
                TextColumn::make('fecha')
                    ->date()
                    ->sortable(),
                TextColumn::make('hora')
                    ->time('h:i a'),
                TextColumn::make('plan.nombre'),
                TextColumn::make('responsables_nombre')
                    ->label('Responsable')
                    ->description(fn (Recepcion $record): string => $record->responsable->telefono ?? '-')
                    ->searchable(),
                TextColumn::make('items_sum_total')
                    ->label('Total')
                    ->sum('items', 'total')
                    ->numeric()
                    ->suffix(' KG')
                    ->alignEnd(),
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
            ]);
    }
}
