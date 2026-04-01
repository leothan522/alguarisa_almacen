<?php

namespace App\Filament\Resources\AjusteEntradas;

use App\Filament\Resources\AjusteEntradas\Pages\CreateAjusteEntrada;
use App\Filament\Resources\AjusteEntradas\Pages\EditAjusteEntrada;
use App\Filament\Resources\AjusteEntradas\Pages\ListAjusteEntradas;
use App\Filament\Resources\AjusteEntradas\Schemas\AjusteEntradaForm;
use App\Filament\Resources\AjusteEntradas\Tables\AjusteEntradasTable;
use App\Models\Recepcion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class AjusteEntradaResource extends Resource
{
    protected static ?string $model = Recepcion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPlusCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Movimientos de Stock';

    protected static ?string $navigationLabel = 'Ajustes de Entrada';

    protected static ?string $slug = 'ajustes-entrada';

    protected static ?string $modelLabel = 'Ajuste';

    protected static ?int $navigationSort = 88;

    protected static ?string $recordTitleAttribute = 'numero';

    public static function form(Schema $schema): Schema
    {
        return AjusteEntradaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AjusteEntradasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAjusteEntradas::route('/'),
            'create' => CreateAjusteEntrada::route('/create'),
            'edit' => EditAjusteEntrada::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
