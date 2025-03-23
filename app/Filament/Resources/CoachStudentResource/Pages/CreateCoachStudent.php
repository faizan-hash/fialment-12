<?php

namespace App\Filament\Resources\CoachStudentResource\Pages;

use App\Filament\Resources\CoachStudentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCoachStudent extends CreateRecord
{
    protected static string $resource = CoachStudentResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
