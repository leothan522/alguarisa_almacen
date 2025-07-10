<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Usuarios';
    protected static ?string $label = 'Usuarios';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?int $navigationSort = 97;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos Básicos')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->maxLength(150),
                        Forms\Components\TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->maxLength(150),
                        Forms\Components\TextInput::make('password')
                            ->label(__('Password'))
                            ->password()
                            ->required()
                            ->maxLength(20)
                            ->hiddenOn('edit'),
                        Forms\Components\TextInput::make('telefono')
                            ->tel()
                            ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Permisos')
                    ->schema([
                        Forms\Components\Toggle::make('access_panel')
                            ->label('Acceso al Dashboard')
                            ->inline(false)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $access_panel = $get('access_panel');
                                if (!$access_panel) {
                                    $set('roles', '');
                                }
                            }),
                        Forms\Components\Select::make('roles')
                            ->relationship('roles', 'name')
                            //->requiredIf('access_panel', true)
                            ->hidden(fn(Get $get) => !$get('access_panel'))
                    ])
                    ->columns(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Verificado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->alignCenter(),
                Tables\Columns\ImageColumn::make('profile_photo_path')
                    ->label('Foto de Perfil')
                    ->circular()
                    //->defaultImageUrl(asset('img/img_placeholder.jpg'))
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label(__('Role')),
                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label(__('Role'))
                    ->relationship('roles', 'name')
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('reset_password')
                        ->label('Restablecer Contraseña')
                        ->icon('heroicon-o-key')
                        ->form([
                            Forms\Components\TextInput::make('password')
                                ->label(__('Password'))
                                ->password()
                                ->required()
                                ->maxLength(20)
                                ->revealable(),
                        ])
                        ->hidden(function (User $record): bool {
                            $response = true;
                            if ($record->id != Auth::id() && !$record->is_root) {
                                $response = false;
                            }
                            return $response;
                        })
                        ->action(function (array $data, User $record): void {
                            $record->password = $data['password'];
                            $record->save();
                            Notification::make()
                                ->title('Guardado exitosamente')
                                ->success()
                                ->send();
                        })
                        ->modalWidth(MaxWidth::ExtraSmall),
                    Tables\Actions\Action::make('email_verified_at')
                        ->label('Verificar Email')
                        ->icon('heroicon-o-check-circle')
                        ->hidden(function (User $record): bool {
                            $response = false;
                            if ($record->email_verified_at) {
                                $response = true;
                            }
                            return $response;
                        })
                        ->action(function (User $record): void {
                            $record->email_verified_at = now();
                            $record->save();
                            Notification::make()
                                ->title('Guardado exitosamente')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->before(function ($record) {
                            $i = 0;
                            do {
                                $repeat = Str::repeat('*', ++$i);
                                $email = $repeat . $record->email;
                                $existe = User::withTrashed()->where('email', $email)->first();
                            } while ($existe);
                            $record->update(['email' => $email]);
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Collection $records) {
                            foreach ($records as $record) {
                                $i = 0;
                                do {
                                    $repeat = Str::repeat('*', ++$i);
                                    $email = $repeat . $record->email;
                                    $existe = User::withTrashed()->where('email', $email)->first();
                                } while ($existe);
                                $record->update(['email' => $email]);
                            }
                        }),
                ]),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn(User $record): bool => $record->id != Auth::id() && !$record->is_root
            );
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
