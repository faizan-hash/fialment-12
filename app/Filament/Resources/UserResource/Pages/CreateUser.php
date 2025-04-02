<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Check if the email already exists
        $emailExists = DB::table('users')->where('email', $data['email'])->exists();
        if ($emailExists) {
            $this->halt();

            Notification::make()
                ->danger()
                ->title('Email Already Exists')
                ->body('A user with this email address already exists in the system.')
                ->send();

            $this->form->fill($data);
            $this->form->addError('email', 'This email is already in use.');

            return new ($this->getModel())();
        }

        return parent::handleRecordCreation($data);
    }
}
