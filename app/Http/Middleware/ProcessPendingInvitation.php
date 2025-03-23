<?php

namespace App\Http\Middleware;

use App\Http\Controllers\TeamInvitationController;
use App\Models\TeamInvitation;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProcessPendingInvitation
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Only process if the user is authenticated
        if (Auth::check()) {
            // Check if there's a pending invitation token in the session
            $token = session('pending_invitation');
            
            \Illuminate\Support\Facades\Log::info('Checking for pending invitation in middleware', [
                'has_token' => !empty($token),
                'token' => $token,
                'user_id' => Auth::id(),
                'user_email' => Auth::user()->email
            ]);
            
            if ($token) {
                // Remove the token from the session
                session()->forget('pending_invitation');
                
                // Find the invitation
                $invitation = TeamInvitation::where('token', $token)
                    ->whereNull('accepted_at')
                    ->whereNull('rejected_at')
                    ->first();
                
                \Illuminate\Support\Facades\Log::info('Found invitation in middleware', [
                    'invitation_found' => $invitation ? true : false,
                    'invitation_id' => $invitation->id ?? null,
                    'invitation_email' => $invitation->email ?? null,
                    'user_email' => Auth::user()->email,
                    'emails_match' => $invitation && $invitation->email === Auth::user()->email
                ]);
                
                if ($invitation && $invitation->email === Auth::user()->email) {
                    // Process the invitation
                    $controller = new TeamInvitationController();
                    return $controller->processInvitationAcceptance($invitation, Auth::user());
                }
            }
        }
        
        return $next($request);
    }
} 