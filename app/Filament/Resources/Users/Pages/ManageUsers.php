<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Notifications\UserCreatedNotification;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ManageUsers extends ManageRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->after(function ($record, $data) {

                // Generate temporary password if none provided
                if (empty($data['password'])) {
                    $temporaryPassword = Str::random(8);
                    $record->update([
                        'password' => Hash::make($temporaryPassword),
                        'must_change_password' => true,
                    ]);
                } else {
                    $temporaryPassword = $data['password'];
                }

                $record->notify(new UserCreatedNotification($temporaryPassword));
            }),
        ];
    }
}
