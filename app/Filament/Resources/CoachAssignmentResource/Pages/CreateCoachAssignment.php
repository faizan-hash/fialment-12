<?php

namespace App\Filament\Resources\CoachAssignmentResource\Pages;

use App\Filament\Resources\CoachAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCoachAssignment extends CreateRecord
{
    protected static string $resource = CoachAssignmentResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
