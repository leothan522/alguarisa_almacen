<?php

namespace App\Filament\Resources\Stocks;

use App\Filament\Resources\Stocks\Pages\ManageStocks;
use App\Models\Stock;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
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

class StockResource extends Resource
{
    protected static ?string $model = Stock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquare3Stack3d;

    protected static string|UnitEnum|null $navigationGroup = 'Almacén';

    protected static ?int $navigationSort = 80;

    protected static ?string $recordTitleAttribute = 'rubros_id';

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return Str::upper($record->rubro->nombre);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['rubro.nombre'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Plan' => $record->plan->nombre,
            'Cant' => formatoMillares($record->asignacion_cantidad + $record->propia_cantidad, 0).' UND',
            'Total' => formatoMillares($record->total).' KG',
        ];
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return self::getUrl('index', [
            'tableAction' => 'view', // Nombre de la acción en tu método table()
            'tableActionRecord' => $record->getKey(), // El ID del registro
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                // FILA SUPERIOR: Contexto General (Full Width)
                Section::make('Última actualización')
                    ->description(fn (Stock $record) => Carbon::parse($record->updated_at)->translatedFormat('d M Y, h:i a'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('almacen.nombre')
                                    ->label('Almacén')
                                    ->icon(Heroicon::OutlinedHome)
                                    ->iconColor('primary')
                                    ->weight(FontWeight::Bold)
                                    ->color('primary'),
                                TextEntry::make('plan.nombre')
                                    ->label('Plan')
                                    ->weight(FontWeight::Bold)
                                    ->color('primary'),
                                TextEntry::make('rubro.nombre')
                                    ->label('Rubro')
                                    ->formatStateUsing(fn (string $state): string => Str::upper($state))
                                    ->weight(FontWeight::Bold)
                                    ->color('primary'),
                            ]),
                    ])
                    ->compact()
                    ->columns(1)
                    ->columnSpanFull()
                    ->compact(),
                // CUERPO CENTRAL: Comparativa de Orígenes (2 Columnas)
                Grid::make(2)
                    ->schema([
                        // Columna Izquierda: Asignación
                        Section::make('Asignación')
                            ->icon(Heroicon::OutlinedArrowDownTray)
                            ->schema([
                                TextEntry::make('und_asignacion')
                                    ->label('Cantidad Unidades')
                                    ->numeric()
                                    ->alignCenter()
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('full_asignacion')
                                    ->label('Peso Total Asignación')
                                    ->numeric(decimalPlaces: 2)
                                    ->suffix(fn (Stock $record): string => ' '.$record->rubro->unidad_medida)
                                    ->alignCenter()
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Black)
                                    ->color('info'), // Azul para lo institucional
                            ])
                            ->compact(),

                        // Columna Derecha: Adquisición Propia
                        Section::make('Adquisición Propia')
                            ->icon(Heroicon::OutlinedBanknotes)
                            ->schema([
                                TextEntry::make('und_propia')
                                    ->label('Cantidad Unidades')
                                    ->numeric()
                                    ->alignCenter()
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('full_propia')
                                    ->label('Peso Total Propio')
                                    ->numeric(decimalPlaces: 2)
                                    ->suffix(fn (Stock $record): string => ' '.$record->rubro->unidad_medida)
                                    ->alignCenter()
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Black)
                                    ->color('success'), // Verde para lo gestionado por ellos
                            ])
                            ->compact(),
                    ])
                    ->columnSpanFull(),
                // SECCIÓN FINAL: Totales Consolidados
                Section::make()
                    ->schema([
                        Grid::make(2) // Dividimos el footer en dos para dar aire
                            ->schema([
                                // TOTAL UNIDADES
                                TextEntry::make('stock_cantidad')
                                    ->label('TOTAL UNIDADES')
                                    ->numeric()
                                    ->suffix(' UND')
                                    ->color('gray') // Un tono más neutro para no competir con el peso
                                    ->weight(FontWeight::Bold)
                                    ->size(TextSize::Large)
                                    ->alignCenter(),

                                // TOTAL PESO (Tu dato estrella)
                                TextEntry::make('stock_total')
                                    ->label('PESO TOTAL')
                                    ->numeric(decimalPlaces: 2)
                                    ->suffix(fn (Stock $record): string => ' '.$record->rubro->unidad_medida)
                                    ->color('violet') // Tu color insignia para el stock real
                                    ->weight(FontWeight::Black)
                                    ->size(TextSize::Large)
                                    ->alignCenter(),
                            ]),
                    ])
                    ->extraAttributes([
                        'class' => 'bg-violet-50/50 dark:bg-violet-950/10 border-t-2 border-violet-500/50',
                    ])
                    ->columnSpanFull()
                    ->compact(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('total', '>', 0))
            ->recordTitleAttribute('rubros_id')
            ->columns([
                TextColumn::make('rubro_movil')
                    ->label('Rubro')
                    ->default(fn (Stock $record): string => Str::upper($record->rubro->nombre))
                    ->description(fn (Stock $record): string => $record->plan->nombre)
                    ->wrap()
                    ->hiddenFrom('md'),
                TextColumn::make('plan.nombre')
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('rubro.nombre')
                    ->formatStateUsing(fn (Stock $record): string => Str::upper($record->rubro->nombre))
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('unidades')
                    ->label('Unidades')
                    ->default(fn (Stock $record): int => $record->stock_cantidad)
                    ->suffix(' UND')
                    ->numeric()
                    ->sortable()
                    ->alignEnd()
                    ->visibleFrom('md'),
                TextColumn::make('full_asignacion')
                    ->label('Asignación')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(fn (Stock $record): string => ' '.$record->rubro->unidad_medida)
                    ->sortable()
                    ->alignEnd()
                    ->visibleFrom('md'),
                TextColumn::make('full_propia')
                    ->label('Propio')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(fn (Stock $record): string => ' '.$record->rubro->unidad_medida)
                    ->sortable()
                    ->alignEnd()
                    ->visibleFrom('md'),
                TextColumn::make('total_movil')
                    ->label('Peso Total')
                    ->default(fn (Stock $record) => $record->stock_total)
                    ->description(fn (Stock $record): string => formatoMillares($record->stock_cantidad, 0).' UND')
                    ->weight(FontWeight::Bold)
                    ->size(TextSize::Medium)
                    ->color('violet')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(fn (Stock $record): string => ' '.$record->rubro->unidad_medida)
                    ->sortable()
                    ->alignEnd()
                    ->hiddenFrom('md'),
                TextColumn::make('stock_total')
                    ->label('Peso Total')
                    ->numeric(decimalPlaces: 2)
                    ->weight(FontWeight::Bold)
                    ->size(TextSize::Medium)
                    ->color('violet')
                    ->suffix(fn (Stock $record): string => ' '.$record->rubro->unidad_medida)
                    ->sortable()
                    ->alignCenter()
                    ->visibleFrom('md'),
            ])
            ->filters([
                SelectFilter::make('planes_id')
                    ->label('Plan')
                    ->relationship('plan', 'nombre'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->modalHeading('Ver Detalles')
                        ->modalWidth(Width::ExtraLarge),
                ]),
            ])
            ->toolbarActions([
                self::actionExportExcel(),
                Action::make('actualizar')
                    ->icon(Heroicon::ArrowPath)
                    ->iconButton(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageStocks::route('/'),
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
                ->withFilename('stocks-export')
                ->withColumns([
                    Column::make('plan.nombre')
                        ->heading('PLAN')
                        ->formatStateUsing(fn ($state) => Str::upper($state)),
                    Column::make('rubro.nombre')
                        ->heading('RUBRO')
                        ->formatStateUsing(fn ($state) => Str::upper($state)),
                    Column::make('rubro.unidad_medida')
                        ->heading('UNIDAD')
                        ->formatStateUsing(fn ($state) => Str::upper($state)),
                    Column::make('asignacion_cantidad')
                        ->heading('ASIGNACIÓN (UND)')
                        ->format(NumberFormat::FORMAT_NUMBER),
                    Column::make('asignacion_total')
                        ->heading('ASIGNACIÓN (PESO)')
                        ->format(NumberFormat::FORMAT_NUMBER_00),
                    Column::make('propia_cantidad')
                        ->heading('PROPIO (UND)')
                        ->format(NumberFormat::FORMAT_NUMBER),
                    Column::make('propia_total')
                        ->heading('PROPIO (PESO)')
                        ->format(NumberFormat::FORMAT_NUMBER_00),
                    Column::make('created_at')
                        ->heading('TOTAL UNIDADES')
                        ->formatStateUsing(fn (Stock $record) => $record->asignacion_cantidad + $record->propia_cantidad)
                        ->format(NumberFormat::FORMAT_NUMBER),
                    Column::make('total')
                        ->heading('PESO TOTAL')
                        ->format(NumberFormat::FORMAT_NUMBER_00),
                ]),
            // ->modifyQueryUsing(fn(Builder $query) => $query->with('items')->orderBy('fecha')),
        ]);
    }
}
