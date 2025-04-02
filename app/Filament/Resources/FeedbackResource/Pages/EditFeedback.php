<?php

namespace App\Filament\Resources\FeedbackResource\Pages;

use App\Filament\Resources\FeedbackResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditFeedback extends EditRecord
{
    protected static string $resource = FeedbackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function afterSaved(): void
    {
        // Display a notification to the user who edited the feedback
        Notification::make()
            ->success()
            ->title('Feedback Updated')
            ->body('The feedback has been successfully updated')
            ->send();
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure the model is marked as modified so observers fire properly
        $this->record->forceFill([
            'comments' => $data['comments'],
            'is_positive' => $data['is_positive'],
        ]);
        
        return $data;
    }
}
