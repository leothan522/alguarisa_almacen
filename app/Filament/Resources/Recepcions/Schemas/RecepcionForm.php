<?php

namespace App\Filament\Resources\Recepcions\Schemas;

use App\Models\Rubro;
use App\Traits\AlmacenSchemas;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Support\Str;

class RecepcionForm
{
    use AlmacenSchemas;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::sectionDatos(),
                self::sectionResponsable(),
                self::sectionRubros(),
                self::sectionObservacion(),
            ]);
    }

    protected static function sectionRubros()
    {
        return Section::make('Rubros')
            ->schema([
                Repeater::make('items')
                    ->label('Rubros')
                    ->hiddenLabel()
                    ->relationship()
                    ->schema([
                        Select::make('rubros_id')
                            ->relationship(name: 'rubro', titleAttribute: 'nombre')
                            ->createOptionForm([
                                TextInput::make('nombre')
                                    ->label('Rubro')
                                    ->maxLength(255)
                                    ->unique()
                                    ->required(),
                                TextInput::make('peso_unitario')
                                    ->label('Peso Unitario')
                                    ->numeric()
                                    ->required(),
                                Select::make('unidad_medida')
                                    ->label('Unidad')
                                    ->options([
                                        'KG' => 'KG',
                                        'LT' => 'LT',
                                    ])
                                    ->required(),
                            ])
                            ->createOptionAction(callback: function (Action $action) {
                                return $action->modalWidth(Width::ExtraSmall);
                            })
                            ->getOptionLabelFromRecordUsing(fn (Rubro $record): string => Str::upper($record->nombre))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(callback: function (?string $state, ?string $old, Set $set) {
                                $rubro = Rubro::find($state);
                                if ($rubro) {
                                    $set('peso_unitario', $rubro->peso_unitario);
                                    $set('rubros_nombre', $rubro->nombre);
                                    $set('rubros_unidad_medida', $rubro->unidad_medida);
                                }
                            }),
                        DatePicker::make('fecha_fabricacion'),
                        DatePicker::make('fecha_vencimiento'),
                        TextInput::make('cantidad_unidades')
                            ->label('Cantidad')
                            ->integer()
                            ->required()
                            ->live(onBlur: true),
                        TextInput::make('peso_unitario')
                            ->label('Peso Unitario')
                            ->numeric()
                            ->step(0.01)
                            ->required()
                            ->live(onBlur: true)
                            ->suffix(function (Get $get, Set $set): string {
                                $cantidad = $get('cantidad_unidades');
                                $peso = $get('peso_unitario');
                                $total = $cantidad * $peso;
                                $set('total', $total);
                                $unidad = $get('rubros_unidad_medida') ?? 'KG';

                                return 'Total: '.formatoMillares($total).' '.$unidad;
                            }),
                        Select::make('tipo_adquisicion')
                            ->label('Tipo adquisición')
                            ->options([
                                'asignacion' => 'ASIGNACIÓN',
                                'propia' => 'PROPIA',
                            ])
                            ->required(),
                        Hidden::make('rubros_nombre'),
                        Hidden::make('rubros_unidad_medida'),
                        Hidden::make('total'),
                    ])
                    ->minItems(1)
                    ->columns(3)
                    ->columnSpanFull(),
            ])
            ->compact()
            ->columns()
            ->columnSpanFull();
    }
}
