<?php

namespace App\Traits;

use App\Models\Despacho;
use App\Models\Parametro;
use App\Models\Plan;
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
    public static bool $recepcion = true;

    public static ?int $plan = null;

    protected static function sectionDatos()
    {
        return Section::make('Datos Básicos')
            ->schema([
                TextInput::make('numero')
                    ->default(function (): string {
                        $num = 1;
                        $formato = '';
                        $nombre = self::$recepcion ? 'numero_recepcion' : 'numero_despacho';
                        $parametro = Parametro::where('nombre', $nombre)->first();
                        if ($parametro) {
                            $formato = $parametro->valor_texto;
                            $num = $parametro->valor_id > 0 ? $parametro->valor_id : $num;
                        }
                        $i = 0;
                        do {
                            $num = $num + $i;
                            $codigo = $formato.cerosIzquierda($num, numSizeCodigo());
                            $existe = self::$recepcion ? Recepcion::where('numero', $codigo)->exists() : Despacho::where('numero', $codigo)->exists();
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
                    ->disabledOn('edit')
                    ->default(self::$plan)
                    ->disableOptionWhen(fn (string $value): bool => $value != self::$plan && ! self::$recepcion),
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
        return Section::make(fn (): string => self::$recepcion ? '¿Quien entrega?' : '¿Quien recibe?')
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

    protected static function getPlan($codigo): mixed
    {
        return Plan::where('codigo', $codigo)->first()?->id;
    }

}
