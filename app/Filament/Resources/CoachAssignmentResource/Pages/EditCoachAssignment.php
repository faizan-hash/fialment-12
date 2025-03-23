<?php

namespace App\Filament\Resources\CoachAssignmentResource\Pages;

use App\Filament\Resources\CoachAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCoachAssignment extends EditRecord
{
    protected static string $resource = CoachAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
