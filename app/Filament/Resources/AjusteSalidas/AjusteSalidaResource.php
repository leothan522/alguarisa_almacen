<?php

namespace App\Filament\Resources\AjusteSalidas;

use App\Filament\Resources\AjusteSalidas\Pages\CreateAjusteSalida;
use App\Filament\Resources\AjusteSalidas\Pages\EditAjusteSalida;
use App\Filament\Resources\AjusteSalidas\Pages\ListAjusteSalidas;
use App\Filament\Resources\AjusteSalidas\Schemas\AjusteSalidaForm;
use App\Filament\Resources\AjusteSalidas\Schemas\AjusteSalidaInfolist;
use App\Filament\Resources\AjusteSalidas\Tables\AjusteSalidasTable;
use App\Models\Despacho;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class AjusteSalidaResource extends Resource
{
    protected static ?string $model = Despacho::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMinusCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Movimientos de Stock';

    protected static ?string $navigationLabel = 'Ajustes de Salida';

    protected static ?string $slug = 'ajustes-salida';

    protected static ?string $modelLabel = 'Ajuste';

    protected static ?int $navigationSort = 89;

    protected static ?string $recordTitleAttribute = 'numero';

    public static function form(Schema $schema): Schema
    {
        return AjusteSalidaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AjusteSalidasTable::configure($table);
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
            'index' => ListAjusteSalidas::route('/'),
            'create' => CreateAjusteSalida::route('/create'),
            'edit' => EditAjusteSalida::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AjusteSalidaInfolist::configure($schema);
    }

    public static function canViewAny(): bool
    {
        return ! auth()->user()->hasRole('Bodega Movil');
    }
}
