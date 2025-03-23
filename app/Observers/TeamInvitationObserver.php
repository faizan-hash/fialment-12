<?php

namespace App\Observers;

use App\Mail\TeamInvitationMail;
use App\Models\TeamInvitation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TeamInvitationObserver
{
    /**
     * Handle the TeamInvitation "created" event.
     */
    public function created(TeamInvitation $invitation): void
    {
        try {
            Mail::to($invitation->email)->send(new TeamInvitationMail($invitation));
        } catch (\Exception $e) {
            Log::error('Failed to send team invitation email: ' . $e->getMessage());
        }
    }

    /**
     * Handle the TeamInvitation "updated" event.
     */
    public function updated(TeamInvitation $invitation): void
    {
        // We could handle resending emails here if needed
    }
} 