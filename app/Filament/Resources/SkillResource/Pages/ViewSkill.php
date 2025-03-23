<?php

namespace App\Filament\Resources\SkillResource\Pages;

use App\Filament\Resources\SkillResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewSkill extends ViewRecord
{
    protected static string $resource = SkillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
    
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Skill Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Skill Name')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('skillArea.name')
                            ->label('Skill Area')
                            ->badge()
                            ->color(fn ($record) => $record->skillArea?->color ?? 'gray'),
                        Infolists\Components\TextEntry::make('description')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Infolists\Components\Section::make('Practices')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('practices')
                            ->schema([
                                Infolists\Components\TextEntry::make('description')
                                    ->label('Practice Description'),
                                Infolists\Components\TextEntry::make('order')
                                    ->label('Order')
                                    ->badge()
                                    ->size('sm'),
                            ])
                            ->columns(3)
                    ])
            ]);
    }
} 