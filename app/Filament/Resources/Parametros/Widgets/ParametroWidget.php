<?php

namespace App\Filament\Resources\Parametros\Widgets;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;

class ParametroWidget extends Widget implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.resources.parametros.widgets.parametro-widget';

    protected static bool $isLazy = false;

    public function parametroInfoList(Schema $schema): Schema
    {
        return $schema
            ->constantState([
                'parametros' => [
                    'size_codigo' => 'valor_id = size, valor_texto = null',
                    'numero_recepcion' => 'valor_id = numero, valor_texto = formato',
                    'numero_despacho' => 'valor_id = numero, valor_texto = formato',
                    'numero_entrada' => 'valor_id = numero, valor_texto = formato',
                    'numero_salida' => 'valor_id = numero, valor_texto = formato',
                ],
            ])
            ->components([
                Section::make('Parametros manuales')
                    ->schema([
                        KeyValueEntry::make('parametros')
                            ->hiddenLabel()
                            ->keyLabel('Nombre')
                            ->valueLabel('Valores'),
                    ])
                    ->compact()
                    ->collapsible(),
            ]);
    }
}
