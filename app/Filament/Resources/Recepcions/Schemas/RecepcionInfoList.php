<?php

namespace App\Filament\Resources\Recepcions\Schemas;

use App\Filament\Resources\Recepcions\Tables\RecepcionsTable;
use App\Models\Recepcion;
use Carbon\Carbon;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconSize;
use Filament\Support\Icons\Heroicon;

class RecepcionInfoList
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('Datos Básicos')
                    ->schema([
                        IconEntry::make('estatus')
                            ->label('Estatus')
                            ->default(true)
                            ->size(IconSize::Large)
                            ->icon(fn (Recepcion $record): Heroicon => match (RecepcionsTable::getEstatus($record)) {
                                'is_complete' => Heroicon::OutlinedDocumentCheck,
                                'is_sealed' => Heroicon::OutlinedCheckBadge,
                                default => Heroicon::OutlinedClock
                            })
                            ->color(fn (Recepcion $record): string => match (RecepcionsTable::getEstatus($record)) {
                                'is_complete' => 'success',
                                'is_sealed' => 'info',
                                default => 'gray'
                            }),
                        TextEntry::make('fecha')
                            ->label('Fecha y Hora')
                            ->formatStateUsing(fn(Recepcion $record): string => Carbon::parse($record->fecha)->translatedFormat('M d, Y') . " " . Carbon::parse($record->hora)->translatedFormat('h:i a'))
                            ->color('primary')
                            ->weight(FontWeight::Bold)
                            ->copyable(),
                        TextEntry::make('numero')
                            ->label('Número')
                            ->color('primary')
                            ->weight(FontWeight::Bold)
                            ->copyable(),
                        TextEntry::make('plan.nombre')
                            ->color('primary')
                            ->weight(FontWeight::Bold)
                            ->copyable(),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
            ]);
    }
}
