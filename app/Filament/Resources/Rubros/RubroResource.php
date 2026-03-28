<?php

namespace App\Filament\Resources\Rubros;

use App\Filament\Resources\Rubros\Pages\ManageRubros;
use App\Models\Rubro;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use pxlrbt\FilamentExcel\Actions\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use UnitEnum;

class RubroResource extends Resource
{
    protected static ?string $model = Rubro::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = 'Almacén';

    protected static ?int $navigationSort = 84;

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static ?int $globalSearchSort = 3;

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return Str::upper($record->nombre);
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Peso' => formatoMillares($record->peso_unitario).' '.$record->unidad_medida,
        ];
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
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
                    ->label('Rubro')
                    ->maxLength(255)
                    ->unique()
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('peso_unitario')
                    ->label('Peso Unitario')
                    ->numeric()
                    ->required()
                    ->columnSpanFull(),
                Select::make('unidad_medida')
                    ->label('Unidad')
                    ->options([
                        'KG' => 'KG',
                        'LT' => 'LT',
                    ])
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombre')
            ->modifyQueryUsing(fn (Builder $query) => $query->orderByDesc('created_at'))
            ->columns([
                TextColumn::make('nombre')
                    ->formatStateUsing(fn (string $state): string => Str::upper($state))
                    ->searchable(),
                TextColumn::make('peso_movil')
                    ->label('Peso Unitario')
                    ->default(fn (Rubro $record) => $record->peso_unitario)
                    ->suffix(fn (Rubro $record): string => ' '.$record->unidad_medida)
                    ->numeric(decimalPlaces: 2)
                    ->alignEnd()
                    ->hiddenFrom('md'),
                TextColumn::make('peso_unitario')
                    ->label('Peso Unitario')
                    ->numeric(decimalPlaces: 2)
                    ->alignEnd()
                    ->visibleFrom('md'),
                TextColumn::make('unidad_medida')
                    ->label('Unidad')
                    ->alignCenter()
                    ->searchable()
                    ->visibleFrom('md'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->modalWidth(Width::ExtraSmall),
                    DeleteAction::make(),
                    ForceDeleteAction::make(),
                    RestoreAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
                self::actionExportExcel(),
                Action::make('actualizar')
                    ->icon(Heroicon::ArrowPath)
                    ->iconButton(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRubros::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    protected static function actionExportExcel()
    {
        return ExportBulkAction::make()->exports([
            ExcelExport::make()
                ->withFilename('rubros-export')
                ->withColumns([
                    Column::make('nombre')
                        ->heading('NOMBRE')
                        ->formatStateUsing(fn ($state) => Str::upper($state)),
                    Column::make('peso_unitario')
                        ->heading('PESO UNITARIO')
                        ->format(NumberFormat::FORMAT_NUMBER_00),
                    Column::make('unidad_medida')
                        ->heading('UNIDAD')
                        ->formatStateUsing(fn ($state) => Str::upper($state)),
                ])
                ->modifyQueryUsing(fn (Builder $query) => $query->orderByDesc('created_at')),
        ]);
    }
}
