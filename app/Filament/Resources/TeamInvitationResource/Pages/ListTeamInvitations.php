<?php

namespace App\Filament\Resources\TeamInvitationResource\Pages;

use App\Filament\Resources\TeamInvitationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
