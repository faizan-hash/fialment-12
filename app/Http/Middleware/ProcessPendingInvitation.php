<?php

namespace App\Http\Middleware;

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

                try {
                    // Find the invitation
                    $invitation = TeamInvitation::where('token', $token)
                        ->whereNull('accepted_at')
                        ->whereNull('rejected_at')
                        ->where('expires_at', '>', now())
                        ->first();

                    if ($invitation) {
                        if ($invitation->email === Auth::user()->email) {
                            // Process the invitation
                            $invitation->update(['accepted_at' => now()]);

                            // Attach user to team
                            $invitation->team->users()->attach(Auth::id());

                            // Assign role
                            Auth::user()->assignRole($invitation->role);

                            // Clear any existing intended URL
                            session()->forget('url.intended');

                            // Direct redirect to /admin without using intended()
                            return redirect('/admin')
                                ->with('success', 'You have successfully joined the team.');
                        } else {
                            // Clear any existing intended URL
                            session()->forget('url.intended');

                            return redirect('/admin')
                                ->with('error', 'This invitation is not for your account.');
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Error processing invitation: ' . $e->getMessage());
                }
            }
        }

        return $next($request);
    }
}
