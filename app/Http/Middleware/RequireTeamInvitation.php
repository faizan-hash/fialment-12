<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TeamInvitation;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class RequireTeamInvitation
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User $user */
        $user = Auth::user();

        // Allow admins to access everything
        if ($user && $user->hasRole('admin')) {
            return $next($request);
        }

        // Check if user has pending invitations
        $pendingInvitations = TeamInvitation::where('email', $user->email)
            ->whereNull('accepted_at')
            ->whereNull('rejected_at')
            ->where('expires_at', '>', now())
            ->exists();

        if ($pendingInvitations) {
            // Redirect to invitation acceptance page
            return redirect()->route('team.invitation.accept');
        }

        return $next($request);
    }
}
