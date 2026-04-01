<?php

namespace App\Filament\Resources\Parametros\Pages;

use App\Filament\Resources\Parametros\ParametroResource;
use App\Filament\Resources\Parametros\Widgets\ParametroWidget;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Width;
use Spatie\Permission\Models\Role;

class ManageParametros extends ManageRecords
{
    protected static string $resource = ParametroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->createAnother(false),
            Action::make('crear-role')
                ->label('Crear Rol')
                ->color('success')
                ->schema([
                    TextInput::make('nombre')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $existe = Role::where('name', $data['nombre'])->exists();
                    if ($existe) {
                        Notification::make()
                            ->title('El Rol ya Existe')
                            ->warning()
                            ->send();
                    } else {
                        Role::create([
                            'name' => $data['nombre'],
                        ]);
                        Notification::make()
                            ->title('Rol Creado')
                            ->success()
                            ->send();
                    }
                })
                ->modalWidth(Width::Small),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            ParametroWidget::class,
        ];
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return 1;
    }
}
