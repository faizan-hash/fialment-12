<?php

namespace App\Filament\Resources\FeedbackResource\Pages;

use App\Filament\Resources\FeedbackResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateFeedback extends CreateRecord
{
    protected static string $resource = FeedbackResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function afterCreated(): void
    {
        // Show a notification to the sender confirming feedback was created
        Notification::make()
            ->success()
            ->title('Feedback Sent')
            ->body('Your feedback has been successfully recorded')
            ->send();
    }
}
