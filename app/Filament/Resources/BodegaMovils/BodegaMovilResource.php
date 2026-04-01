<?php

namespace App\Filament\Resources\BodegaMovils;

use App\Filament\Resources\BodegaMovils\Pages\CreateBodegaMovil;
use App\Filament\Resources\BodegaMovils\Pages\EditBodegaMovil;
use App\Filament\Resources\BodegaMovils\Pages\ListBodegaMovils;
use App\Filament\Resources\BodegaMovils\Schemas\BodegaMovilForm;
use App\Filament\Resources\BodegaMovils\Tables\BodegaMovilsTable;
use App\Models\Despacho;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class BodegaMovilResource extends Resource
{
    protected static ?string $model = Despacho::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static string|UnitEnum|null $navigationGroup = 'Gestión de Almacén';

    protected static ?string $slug = 'bodega-movil';

    protected static ?string $navigationLabel = 'Bodega Móvil';

    protected static ?int $navigationSort = 82;

    protected static ?string $recordTitleAttribute = 'numero';

    public static function form(Schema $schema): Schema
    {
        return BodegaMovilForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BodegaMovilsTable::configure($table);
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
            'index' => ListBodegaMovils::route('/'),
            'create' => CreateBodegaMovil::route('/create'),
            'edit' => EditBodegaMovil::route('/{record}/edit'),
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
