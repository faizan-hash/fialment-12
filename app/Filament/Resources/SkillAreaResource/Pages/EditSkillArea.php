<?php

namespace App\Filament\Resources\SkillAreaResource\Pages;

use App\Filament\Resources\SkillAreaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSkillArea extends EditRecord
{
    protected static string $resource = SkillAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
} 