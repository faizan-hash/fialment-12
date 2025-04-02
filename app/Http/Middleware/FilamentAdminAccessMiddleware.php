<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class FilamentAdminAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Allow access if user is admin
        if ($user && $user->hasRole('admin')) {
            // Override the default dashboard URL
            config(['filament.default_redirect_url' => '/admin']);
            return $next($request);
        }

        // Check if user has accepted invitations (is part of a team)
        if ($user && $user->teams()->count() > 0) {
            // Override the default dashboard URL
            config(['filament.default_redirect_url' => '/admin']);
            return $next($request);
        }

        // Redirect unauthorized users to login with a message
        Auth::logout();
        return redirect()->route('login')
            ->with('error', 'You need to be invited to a team before you can access the dashboard. Please contact an administrator for an invitation.');
    }
}
