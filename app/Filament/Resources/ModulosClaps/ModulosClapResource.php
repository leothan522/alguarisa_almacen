<?php

namespace App\Filament\Resources\ModulosClaps;

use App\Filament\Resources\ModulosClaps\Pages\CreateModulosClap;
use App\Filament\Resources\ModulosClaps\Pages\EditModulosClap;
use App\Filament\Resources\ModulosClaps\Pages\ListModulosClaps;
use App\Filament\Resources\ModulosClaps\Schemas\ModulosClapForm;
use App\Filament\Resources\ModulosClaps\Tables\ModulosClapsTable;
use App\Models\Despacho;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ModulosClapResource extends Resource
{
    protected static ?string $model = Despacho::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string | UnitEnum | null $navigationGroup = 'Gestión de Almacén';

    protected static ?string $navigationLabel = 'Módulos CLAP';

    protected static ?int $navigationSort = 83;

    protected static ?string $recordTitleAttribute = 'numero';

    public static function form(Schema $schema): Schema
    {
        return ModulosClapForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ModulosClapsTable::configure($table);
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
            'index' => ListModulosClaps::route('/'),
            'create' => CreateModulosClap::route('/create'),
            'edit' => EditModulosClap::route('/{record}/edit'),
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
