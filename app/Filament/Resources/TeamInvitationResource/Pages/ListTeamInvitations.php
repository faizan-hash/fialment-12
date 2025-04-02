<?php

namespace App\Filament\Resources\TeamInvitationResource\Pages;

use App\Filament\Resources\TeamInvitationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;

class ListTeamInvitations extends ListRecords
{
    protected static string $resource = TeamInvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
