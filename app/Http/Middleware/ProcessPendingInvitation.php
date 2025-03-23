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
            
            if ($token) {
                // Remove the token from the session
                session()->forget('pending_invitation');
                
                // Find the invitation
                $invitation = TeamInvitation::where('token', $token)
                    ->whereNull('accepted_at')
                    ->whereNull('rejected_at')
                    ->first();
                
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