<?php

namespace App\Filament\Resources\Recepcions;

use App\Filament\Resources\Recepcions\Pages\CreateRecepcion;
use App\Filament\Resources\Recepcions\Pages\EditRecepcion;
use App\Filament\Resources\Recepcions\Pages\ListRecepcions;
use App\Filament\Resources\Recepcions\Schemas\RecepcionForm;
use App\Filament\Resources\Recepcions\Schemas\RecepcionInfoList;
use App\Filament\Resources\Recepcions\Tables\RecepcionsTable;
use App\Models\Recepcion;
use BackedEnum;
use Carbon\Carbon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class RecepcionResource extends Resource
{
    protected static ?string $model = Recepcion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static ?string $slug = 'Recepciones';

    protected static ?string $modelLabel = 'Recepción';

    protected static ?string $pluralModelLabel = 'Recepciones';

    protected static string|UnitEnum|null $navigationGroup = 'Almacén';

    protected static ?string $recordTitleAttribute = 'numero';

    public static function getGloballySearchableAttributes(): array
    {
        return ['numero', 'fecha'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Fecha' => Carbon::parse($record->fecha)->translatedFormat('M d, Y'),
            'Plan' => $record->plan->nombre,
            'Total' => formatoMillares($record->total_peso).' KG',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return RecepcionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RecepcionsTable::configure($table);
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
            'index' => ListRecepcions::route('/'),
            'create' => CreateRecepcion::route('/create'),
            'edit' => EditRecepcion::route('/{record}/edit'),
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
        return RecepcionInfoList::configure($schema);
    }
}
