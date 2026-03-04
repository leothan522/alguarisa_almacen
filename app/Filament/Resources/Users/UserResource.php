<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 90;

    protected static ?string $modelLabel = 'usuario';

    protected static ?string $slug = 'usuarios';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $globalSearchSort = 90;

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return Str::ucwords($record->name);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return ['Email' => Str::lower($record->email)];
    }

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
                Fieldset::make('Datos Básicos')
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->maxLength(255)
                            ->required(),
                        TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->maxLength(255)
                            ->unique()
                            ->required(),
                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                            ->required(),
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->maxLength(15)
                            ->required()
                            ->hiddenOn('edit'),
                    ])
                    ->dense()
                    ->columnSpanFull(),
                Fieldset::make('Permisos')
                    ->schema([
                        Toggle::make('is_active')
                            ->inline(false)
                            ->hiddenOn('create'),
                        Select::make('roles')
                            ->label(__('Role'))
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload(),
                    ])
                    ->dense()
                    ->columnSpanFull(),
                Text::make(fn (User $record): string => $record->login_count.' Visitas')
                    ->size(TextSize::Medium)
                    ->badge()
                    ->icon(Heroicon::OutlinedFlag)
                    ->hiddenOn('create'),
                Hidden::make('access_panel')
                    ->default(true)
                    ->disabledOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('user')
                    ->label(__('User'))
                    ->default(fn (User $record): string => Str::ucwords($record->name))
                    ->description(fn (User $record): string => Str::lower($record->email))
                    ->wrap()
                    ->icon(fn (User $record): Heroicon => match (self::getEstatus($record)) {
                        'activo' => Heroicon::OutlinedShieldCheck,
                        'inactivo' => Heroicon::OutlinedNoSymbol,
                        default => Heroicon::OutlinedClock
                    })
                    ->iconColor(fn (User $record): string => match (self::getEstatus($record)) {
                        'activo' => 'success',
                        'inactivo' => 'danger',
                        default => 'gray'
                    })
                    ->hiddenFrom('md'),
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->formatStateUsing(fn (string $state): string => Str::ucwords($state))
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('email')
                    ->label(__('Email Address'))
                    ->searchable()
                    ->visibleFrom('md'),
                IconColumn::make('estatus')
                    ->label('Estatus')
                    ->default(fn (User $record): string => self::getEstatus($record))
                    ->icon(fn (string $state): Heroicon => match ($state) {
                        'activo' => Heroicon::OutlinedShieldCheck,
                        'inactivo' => Heroicon::OutlinedNoSymbol,
                        default => Heroicon::OutlinedClock
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'activo' => 'success',
                        'inactivo' => 'danger',
                        default => 'gray'
                    })
                    ->alignCenter()
                    ->visibleFrom('md'),
                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->alignCenter()
                    ->visibleFrom('md'),
                TextColumn::make('login_count')
                    ->label('Visitas')
                    ->icon(Heroicon::OutlinedFlag)
                    ->numeric()
                    ->alignCenter()
                    ->visibleFrom('md'),
                ToggleColumn::make('is_active')
                    ->alignCenter()
                    ->disabled(fn (User $record): bool => self::isDisabled($record))
                    ->visibleFrom('md'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('reset_password')
                        ->label(__('Reset Password'))
                        ->icon(Heroicon::OutlinedKey)
                        ->schema([
                            TextInput::make('new_password')
                                ->label(__('New Password'))
                                ->password()
                                ->revealable()
                                ->minLength(8)
                                ->maxLength(15)
                                ->required(),
                        ])
                        ->action(function (array $data, User $record): void {
                            $record->password = Hash::make($data['new_password']);
                            $record->save();
                        })
                        ->modalWidth(Width::ExtraSmall)
                        ->hidden(fn (User $record): bool => self::isDisabled($record))
                        ->disabled(fn (User $record): bool => self::isDisabled($record)),
                    Action::make('validar_email')
                        ->label('Verificar Email')
                        ->icon(Heroicon::CheckCircle)
                        ->action(function (User $record): void {
                            $record->email_verified_at = now();
                            $record->save();
                        })
                        ->requiresConfirmation()
                        ->hidden(fn (User $record): bool => ! is_null($record->email_verified_at))
                        ->disabled(fn (User $record): bool => self::isDisabled($record)),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorizeIndividualRecords('delete'),
                    ForceDeleteBulkAction::make()
                        ->authorizeIndividualRecords('forceDelete'),
                    RestoreBulkAction::make(),
                ]),
                Action::make('actualizar')
                    ->icon(Heroicon::ArrowPath)
                    ->iconButton(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }

    protected static function getEstatus($record): string
    {
        $validado = $record->email_verified_at ?? false;
        $response = 'default';
        if ($validado) {
            if ($record->is_active || $record->is_root) {
                $response = 'activo';
            } else {
                $response = 'inactivo';
            }
        }

        return $response;
    }

    protected static function isDisabled($record): bool
    {
        return (auth()->id() == $record->id) || ! isAdmin() || $record->is_root;
    }
}
