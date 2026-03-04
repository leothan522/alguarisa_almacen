<?php

namespace App\Filament\Resources\Recepcions\Tables;

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
                    ->time()
                    ->sortable(),
                TextColumn::make('almacenes_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('planes_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('jefes_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('jefes_nombre')
                    ->searchable(),
                TextColumn::make('jefes_cedula')
                    ->searchable(),
                TextColumn::make('responsables_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('responsables_nombre')
                    ->searchable(),
                TextColumn::make('responsables_cedula')
                    ->searchable(),
                TextColumn::make('responsables_telefono')
                    ->searchable(),
                TextColumn::make('responsables_empresa')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
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
