<?php

namespace App\Http\Controllers;

use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeamInvitationController extends Controller
{
    /**
     * Accept a team invitation.
     */
    public function accept(Request $request, string $token)
    {
        // Find the invitation by token
        $invitation = TeamInvitation::where('token', $token)
            ->whereNull('accepted_at')
            ->whereNull('rejected_at')
            ->first();
        
        if (!$invitation) {
            return redirect()->route('login')
                ->with('error', 'The invitation is invalid or has already been used.');
        }
        
        // Check if the invitation has expired
        if ($invitation->isExpired()) {
            return redirect()->route('login')
                ->with('error', 'This invitation has expired.');
        }
        
        // Check if the user is already logged in
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if the logged-in user's email matches the invitation email
            if ($user->email !== $invitation->email) {
                return redirect()->route('dashboard')
                    ->with('error', 'This invitation was sent to a different email address.');
            }
            
            // Process the invitation acceptance
            return $this->processInvitationAcceptance($invitation, $user);
        }
        
        // Check if a user with the invitation email already exists
        $user = User::where('email', $invitation->email)->first();
        
        if ($user) {
            // User exists but is not logged in
            session(['pending_invitation' => $token]);
            return redirect()->route('login')
                ->with('info', 'Please log in to accept the invitation.');
        }
        
        // No user exists with this email, store the token in session and redirect to registration
        session(['pending_invitation' => $token]);
        return redirect()->route('register')
            ->with('info', 'Please create an account to join the team.');
    }
    
    /**
     * Process the invitation acceptance.
     */
    public function processInvitationAcceptance(TeamInvitation $invitation, User $user)
    {
        try {
            // First, directly update the invitation accepted_at timestamp
            // Use direct query builder instead of Eloquent to ensure update happens
            $updated = DB::table('team_invitations')
                ->where('id', $invitation->id)
                ->update(['accepted_at' => now()]);
            
            // Then attach the user to the team
            if (!$user->teams->contains($invitation->team_id)) {
                $user->teams()->attach($invitation->team_id, ['role' => $invitation->role]);
            }
            
            // Assign role to user if they don't already have it
            if (!$user->hasRole($invitation->role)) {
                $user->assignRole($invitation->role);
            }
            
            return redirect()->route('filament.admin.pages.dashboard')
                ->with('success', 'You have successfully joined the team: ' . $invitation->team->name);
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('Invitation processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'invitation_id' => $invitation->id ?? null,
                'user_id' => $user->id
            ]);
            
            return redirect()->route('filament.admin.pages.dashboard')
                ->with('error', 'An error occurred while processing your invitation: ' . $e->getMessage());
        }
    }
} 