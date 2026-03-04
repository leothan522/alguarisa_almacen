<?php

namespace App\Filament\Resources\Recepcions\Schemas;

use App\Models\Responsable;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Support\Str;

class RecepcionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos Básicos')
                    ->schema([
                        TextInput::make('numero')
                            ->required(),
                        Select::make('planes_id')
                            ->label('Plan')
                            ->relationship(name: 'plan', titleAttribute: 'nombre')
                            ->required(),
                        DatePicker::make('fecha')
                            ->required(),
                        TimePicker::make('hora')
                            ->required(),
                    ])
                    ->compact()
                    ->columns(),
                Section::make('¿Quien entrega? ')
                    ->schema([
                        Select::make('responsables_id')
                            ->relationship(name: 'responsable', titleAttribute: 'nombre', ignoreRecord: true)
                            ->createOptionForm([
                                TextInput::make('cedula')
                                    ->label('Cédula')
                                    ->integer()
                                    ->unique()
                                    ->required(),
                                TextInput::make('nombre')
                                    ->label('Nombre y Apellido')
                                    ->required(),
                                TextInput::make('telefono')
                                    ->label('Teléfono')
                                    ->tel()
                                    ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/'),
                                TextInput::make('empresa')
                            ])
                            ->createOptionAction(function (Action $action) {
                                return $action->modalWidth(Width::ExtraSmall);
                            })
                            ->getOptionLabelFromRecordUsing(fn(Responsable $record): string => Str::upper(formatoMillares($record->cedula, 0) . " " . $record->nombre))
                            ->searchable(['nombre', 'cedula'])
                            ->preload()
                            ->required(),
                    ])
                    ->compact(),
                Section::make('Observación')
                    ->schema([
                        Textarea::make('observacion')
                            ->hiddenLabel()
                            ->columnSpanFull(),
                    ])
                    ->compact()
                    ->columnSpanFull(),
            ]);
    }
}
