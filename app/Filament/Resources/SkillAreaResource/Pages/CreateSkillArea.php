<?php

namespace App\Filament\Resources\SkillAreaResource\Pages;

use App\Filament\Resources\SkillAreaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSkillArea extends CreateRecord
{
    protected static string $resource = SkillAreaResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
} 