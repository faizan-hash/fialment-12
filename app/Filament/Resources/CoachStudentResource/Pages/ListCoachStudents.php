<?php

namespace App\Filament\Resources\CoachStudentResource\Pages;

use App\Filament\Resources\CoachStudentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCoachStudents extends ListRecords
{
    protected static string $resource = CoachStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
