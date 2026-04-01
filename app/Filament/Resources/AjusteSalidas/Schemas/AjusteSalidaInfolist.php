<?php

namespace App\Filament\Resources\AjusteSalidas\Schemas;

use App\Filament\Resources\Recepcions\Tables\RecepcionsTable;
use App\Models\Despacho;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
use App\Filament\Resources\AjusteSalidas\Tables\AjusteSalidasTable;

class AjusteSalidaInfolist
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
                            ->icon(fn (Despacho $record): Heroicon => match (AjusteSalidasTable::getEstatus($record)) {
                                'is_complete' => Heroicon::OutlinedDocumentCheck,
                                'is_sealed' => Heroicon::OutlinedCheckBadge,
                                default => Heroicon::OutlinedClock
                            })
                            ->color(fn (Despacho $record): string => match (AjusteSalidasTable::getEstatus($record)) {
                                'is_complete' => 'success',
                                'is_sealed' => 'info',
                                default => 'gray'
                            }),
                        TextEntry::make('fecha')
                            ->label('Fecha y Hora')
                            ->date()
                            ->belowContent(fn (Despacho $record): string => Carbon::parse($record->hora)->translatedFormat('h:i a'))
                            ->color('primary')
                            ->weight(FontWeight::Bold)
                            ->copyable(),
                        TextEntry::make('numero')
                            ->label('Número y Plan')
                            ->belowContent(fn (Despacho $record): string => $record->plan->nombre)
                            ->color('primary')
                            ->weight(FontWeight::Bold)
                            ->copyable(),
                        TextEntry::make('total')
                            ->label('Salida Total')
                            ->default(fn (Despacho $record): string => formatoMillares($record->detalles->sum('total')).' KG')
                            ->color('primary')
                            ->size(TextSize::Large)
                            ->weight(FontWeight::ExtraBold)
                            ->belowContent(fn (Despacho $record): string => formatoMillares($record->detalles->sum('cantidad_unidades'), 0).' UND')
                            ->copyable(),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
                Fieldset::make('Memoria Fotográfica')
                    ->schema([
                        self::makeImageEntry('image_documento'),
                        self::makeImageEntry('image_1'),
                        self::makeImageEntry('image_2'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }

    protected static function makeImageEntry(string $name): ImageEntry
    {
        return ImageEntry::make($name)
            ->hiddenLabel()
            ->defaultImageUrl(asset('img/placeholder.jpg'))
            ->disk('public')
            ->visibility('public')
            ->imageSize(200)
            ->alignCenter()
            ->extraImgAttributes([
                'class' => 'md:cursor-zoom-in md:hover:opacity-80 transition shadow-md rounded-xl',
                // Solo permitimos el evento si la pantalla es de escritorio (>= 768px)
                'x-on:click' => 'window.innerWidth < 768 ? $event.stopImmediatePropagation() : null',
            ])
            // Definimos la acción que abre el modal
            ->action(
                Action::make('viewImage')
                    ->modalHeading('Vista Previa')
                    ->modalSubmitAction(false) // No necesitamos botón de guardado
                    ->modalCancelActionLabel('Cerrar')
                    ->modalContent(fn ($record) => new HtmlString('
                    <div class="flex justify-center items-center p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                        <img src="'.verImagen($record->$name).'"
                             class="max-w-full h-auto rounded-lg shadow-2xl border border-gray-200 dark:border-gray-700"
                             style="max-height: 80vh; object-fit: contain;" alt="" />
                    </div>
                '))
            );
    }
}
