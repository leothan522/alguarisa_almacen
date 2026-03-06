<?php

namespace App\Filament\Resources\Recepcions\Schemas;

use App\Filament\Resources\Recepcions\Tables\RecepcionsTable;
use App\Models\Recepcion;
use Carbon\Carbon;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\TextSize;
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
                            ->size(IconSize::ExtraLarge)
                            ->icon(fn(Recepcion $record): Heroicon => match (RecepcionsTable::getEstatus($record)) {
                                'is_complete' => Heroicon::OutlinedDocumentCheck,
                                'is_sealed' => Heroicon::OutlinedCheckBadge,
                                default => Heroicon::OutlinedClock
                            })
                            ->color(fn(Recepcion $record): string => match (RecepcionsTable::getEstatus($record)) {
                                'is_complete' => 'success',
                                'is_sealed' => 'info',
                                default => 'gray'
                            }),
                        TextEntry::make('fecha')
                            ->label('Fecha y Hora')
                            ->date()
                            ->belowContent(fn(Recepcion $record): string => Carbon::parse($record->hora)->translatedFormat('h:i a'))
                            ->color('primary')
                            ->weight(FontWeight::Bold)
                            ->copyable(),
                        TextEntry::make('numero')
                            ->label('Número y Plan')
                            ->belowContent(fn(Recepcion $record): string => $record->plan->nombre)
                            ->color('primary')
                            ->weight(FontWeight::Bold)
                            ->copyable(),
                        TextEntry::make('total')
                            ->label('Recepción Total')
                            ->default(fn(Recepcion $record): string => formatoMillares($record->items->sum('total')) . " KG")
                            ->color('primary')
                            ->size(TextSize::Large)
                            ->weight(FontWeight::ExtraBold)
                            ->belowContent(fn(Recepcion $record): string => formatoMillares($record->items->sum('cantidad_unidades'), 0) . " UND")
                            ->copyable(),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
                Fieldset::make('Memoria Fotográfica')
                    ->schema([
                        ImageEntry::make('image_documento')
                            ->hiddenLabel()
                            ->disk('public')
                            ->visibility('public')
                            ->imageSize(200)
                            ->alignCenter(),
                        ImageEntry::make('image_1')
                            ->hiddenLabel()
                            ->disk('public')
                            ->visibility('public')
                            ->imageSize(200)
                            ->alignCenter(),
                        ImageEntry::make('image_2')
                            ->hiddenLabel()
                            ->disk('public')
                            ->visibility('public')
                            ->imageSize(200)
                            ->alignCenter(),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
            ]);
    }
}
