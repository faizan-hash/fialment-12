<?php

namespace App\Filament\Resources\CoachAssignmentResource\Pages;

use App\Filament\Resources\CoachAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCoachAssignments extends ListRecords
{
    protected static string $resource = CoachAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
