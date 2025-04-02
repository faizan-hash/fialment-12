<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DashboardAccessMiddleware
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
            return $next($request);
        }

        // Allow access if user is part of at least one team (which means they've accepted an invitation)
        if ($user && $user->teams()->count() > 0) {
            return $next($request);
        }

        // Redirect other users to a page explaining they need an invitation
        return redirect()->route('login')
            ->with('error', 'Access to the dashboard requires an invitation. Please contact an administrator if you need access.');
    }
}
