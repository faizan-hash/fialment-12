<?php

namespace App\Filament\Resources\SkillAreaResource\Pages;

use App\Filament\Resources\SkillAreaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSkillAreas extends ListRecords
{
    protected static string $resource = SkillAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 