<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function ($record) {
                    $i = 0;
                    do {
                        $repeat = Str::repeat('*', ++$i);
                        $email = $repeat . $record->email;
                        $existe = User::withTrashed()->where('email', $email)->first();
                    } while ($existe);
                    $record->update(['email' => $email]);
                }),
        ];
    }
}
