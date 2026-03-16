<?php

namespace App\Traits;

use App\Models\Despacho;
use App\Models\Parametro;
use App\Models\Recepcion;
use App\Models\Responsable;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Width;
use Illuminate\Support\Str;

trait AlmacenSchemas
{
    protected static function sectionDatos($recepcion = true)
    {
        return Section::make('Datos Básicos')
            ->schema([
                TextInput::make('numero')
                    ->default(function () use ($recepcion): string {
                        $num = 1;
                        $formato = '';
                        $nombre = $recepcion ? 'numero_recepcion' : 'numero_despacho';
                        $parametro = Parametro::where('nombre', $nombre)->first();
                        if ($parametro) {
                            $formato = $parametro->valor_texto;
                            $num = $parametro->valor_id > 0 ? $parametro->valor_id : $num;
                        }
                        $i = 0;
                        do {
                            $num = $num + $i;
                            $codigo = $formato.cerosIzquierda($num, numSizeCodigo());
                            $existe = $recepcion ? Recepcion::where('numero', $codigo)->exists() : Despacho::where('numero', $codigo)->exists();
                            $i++;
                        } while ($existe);

                        return $codigo;
                    })
                    ->unique()
                    ->required(),
                Select::make('planes_id')
                    ->label('Plan')
                    ->relationship(name: 'plan', titleAttribute: 'nombre')
                    ->required()
                    ->disabledOn('edit'),
                DatePicker::make('fecha')
                    ->default(now())
                    ->required(),
                TimePicker::make('hora')
                    ->default(now())
                    ->seconds(false)
                    ->required(),
            ])
            ->compact()
            ->columns();
    }

    protected static function sectionResponsable()
    {
        return Section::make('¿Quien entrega? ')
            ->schema([
                Select::make('responsables_id')
                    ->relationship(name: 'responsable', titleAttribute: 'nombre')
                    ->createOptionForm(self::formResponsable())
                    ->createOptionAction(function (Action $action) {
                        return $action->modalWidth(Width::ExtraSmall);
                    })
                    ->editOptionForm(self::formResponsable())
                    ->editOptionAction(function (Action $action) {
                        return $action->modalWidth(Width::ExtraSmall);
                    })
                    ->getOptionLabelFromRecordUsing(fn (Responsable $record): string => Str::upper(formatoMillares($record->cedula, 0).' '.$record->nombre))
                    ->searchable(['nombre', 'cedula'])
                    ->preload()
                    ->required(),
            ])
            ->compact();
    }

    protected static function sectionObservacion()
    {
        return Section::make('Observación')
            ->schema([
                Textarea::make('observacion')
                    ->hiddenLabel()
                    ->columnSpanFull(),
            ])
            ->compact()
            ->columnSpanFull();
    }

    protected static function formResponsable(): array
    {
        return [
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
            TextInput::make('empresa'),
        ];
    }

}
