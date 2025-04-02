<?php

namespace App\Observers;

use App\Mail\TeamInvitationMail;
use App\Models\TeamInvitation;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class TeamInvitationObserver
{
    /**
     * Handle the TeamInvitation "created" event.
     *
     * NOTE: Email sending is disabled here to prevent duplication.
     * All emails are now sent directly from the controllers and relation managers.
     */
    public function created(TeamInvitation $invitation): void
    {
        // Email sending is completely disabled in the observer
        // to prevent duplicate emails
        return;
    }

    /**
     * Handle the TeamInvitation "updated" event.
     */
    public function updated(TeamInvitation $invitation): void
    {
        // Notify team admins when an invitation is accepted or rejected
        if ($invitation->isDirty('accepted_at') && $invitation->accepted_at) {
            // Get the user who accepted the invitation
            $acceptedUser = User::where('email', $invitation->email)->first();
            if (!$acceptedUser) return;

            // Notify the inviter and team admins
            $notifyUsers = User::role('admin')
                ->whereHas('teams', function ($query) use ($invitation) {
                    $query->where('teams.id', $invitation->team_id);
                })
                ->get();

            foreach ($notifyUsers as $admin) {
                Notification::make()
                    ->success()
                    ->title('Team Invitation Accepted')
                    ->body($acceptedUser->name . ' has joined ' . $invitation->team->name)
                    ->icon('heroicon-o-check-circle')
                    ->actions([
                        Notification\Actions\Action::make('view')
                            ->button()
                            ->url(route('filament.admin.resources.teams.edit', ['record' => $invitation->team_id]))
                    ])
                    ->sendToDatabase($admin);
            }
        } elseif ($invitation->isDirty('rejected_at') && $invitation->rejected_at) {
            // Notify the inviter when invitation is rejected
            $inviter = $invitation->inviter;
            if ($inviter) {
                Notification::make()
                    ->warning()
                    ->title('Team Invitation Rejected')
                    ->body('Invitation to ' . $invitation->email . ' for ' . $invitation->team->name . ' team has been declined')
                    ->icon('heroicon-o-x-circle')
                    ->sendToDatabase($inviter);
            }
        }
    }
}
