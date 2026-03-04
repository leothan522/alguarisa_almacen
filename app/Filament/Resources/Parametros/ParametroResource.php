<?php

namespace App\Filament\Resources\Parametros;

use App\Filament\Resources\Parametros\Pages\ManageParametros;
use App\Filament\Resources\Parametros\Widgets\ParametroWidget;
use App\Models\Parametro;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ParametroResource extends Resource
{
    protected static ?string $model = Parametro::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog;

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 99;

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static ?int $globalSearchSort = 99;

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        // Usamos los parámetros nativos de Filament 5 para abrir el modal automáticamente
        return self::getUrl('index', [
            'tableAction' => 'edit', // Nombre de la acción en tu método table()
            'tableActionRecord' => $record->getKey(), // El ID del registro
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->maxLength(255)
                    ->required(),
                TextInput::make('valor_id')
                    ->numeric(),
                Textarea::make('valor_texto')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombre')
            ->columns([
                TextColumn::make('nombre')
                    ->searchable(),
                TextColumn::make('valor_id')
                    ->label('valor_id')
                    ->numeric()
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('valor_texto')
                    ->label('valor_texto')
                    ->searchable()
                    ->limit(30)
                    ->visibleFrom('md'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
                Action::make('actualizar')
                    ->icon(Heroicon::ArrowPath)
                    ->iconButton(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageParametros::route('/'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            ParametroWidget::class,
        ];
    }
}
