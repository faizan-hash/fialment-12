<?php

namespace App\Observers;

use App\Models\Feedback;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class FeedbackObserver
{
    /**
     * Handle the Feedback "created" event.
     */
    public function created(Feedback $feedback): void
    {
        try {
            // Get the recipient user
            $recipient = $feedback->recipient;
            
            // Send a notification to the recipient
            if ($recipient) {
                Log::info('Sending notification for new feedback', [
                    'feedback_id' => $feedback->id,
                    'recipient_id' => $recipient->id,
                    'sender_id' => $feedback->sender_id
                ]);
                
                Notification::make()
                    ->title('New Feedback Received')
                    ->body('You have received new feedback from ' . $feedback->sender->name)
                    ->icon($feedback->is_positive ? 'heroicon-o-check-circle' : 'heroicon-o-information-circle')
                    ->iconColor($feedback->is_positive ? 'success' : 'warning')
                    ->actions([
                        Action::make('view')
                            ->button()
                            ->url(route('filament.admin.resources.feedback.index'))
                    ])
                    ->sendToDatabase($recipient);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send feedback notification: ' . $e->getMessage(), [
                'exception' => $e,
                'feedback_id' => $feedback->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle the Feedback "updated" event.
     */
    public function updated(Feedback $feedback): void
    {
        Log::info('Feedback updated event triggered', [
            'feedback_id' => $feedback->id,
            'is_dirty' => $feedback->isDirty(),
            'dirty_attributes' => $feedback->getDirty(),
            'original_comments' => $feedback->getOriginal('comments'),
            'original_is_positive' => $feedback->getOriginal('is_positive')
        ]);
        
        // Notify about feedback updates when important fields change
        if ($feedback->isDirty(['comments', 'is_positive'])) {
            try {
                // Get the recipient user
                $recipient = $feedback->recipient;
                
                // Send a notification to the recipient
                if ($recipient) {
                    Log::info('Sending notification for updated feedback', [
                        'feedback_id' => $feedback->id,
                        'recipient_id' => $recipient->id
                    ]);
                    
                    Notification::make()
                        ->title('Feedback Updated')
                        ->body('Your feedback from ' . $feedback->sender->name . ' has been updated')
                        ->icon('heroicon-o-pencil')
                        ->iconColor('info')
                        ->actions([
                            Action::make('view')
                                ->button()
                                ->url(route('filament.admin.resources.feedback.index'))
                        ])
                        ->sendToDatabase($recipient);
                } else {
                    Log::warning('Recipient not found for feedback update notification', [
                        'feedback_id' => $feedback->id,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send feedback update notification: ' . $e->getMessage(), [
                    'exception' => $e,
                    'feedback_id' => $feedback->id ?? null,
                    'trace' => $e->getTraceAsString()
                ]);
            }
        } else {
            Log::info('No relevant changes detected in feedback update', [
                'feedback_id' => $feedback->id
            ]);
        }
    }
} 