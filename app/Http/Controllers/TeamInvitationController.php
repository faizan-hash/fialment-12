<?php

namespace App\Http\Controllers;

use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class TeamInvitationController extends Controller
{
    /**
     * Accept a team invitation.
     */
    public function accept(string $token)
    {
        // Log the full request URL and the extracted token for debugging
        $fullUrl = request()->fullUrl();
        \Illuminate\Support\Facades\Log::info('Invitation accept route hit', [
            'token' => $token,
            'full_url' => $fullUrl,
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'none',
            'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'none'
        ]);

        try {
            // Clean the token in case it has URL encoding or extra characters
            $token = trim($token);

            $invitation = TeamInvitation::where('token', $token)
                ->whereNull('accepted_at')
                ->whereNull('rejected_at')
                ->where('expires_at', '>', now())
                ->first();

            if (!$invitation) {
                // Log failure to find the invitation
                \Illuminate\Support\Facades\Log::error('Invitation not found for token: ' . $token);
                throw new \Exception('Invitation not found');
            }

            \Illuminate\Support\Facades\Log::info('Invitation found', [
                'invitation_id' => $invitation->id,
                'invitation_email' => $invitation->email,
                'team_id' => $invitation->team_id,
                'token_length' => strlen($token),
                'original_token_length' => strlen($invitation->token),
                'expires_at' => $invitation->expires_at->format('Y-m-d H:i:s'),
            ]);

            // If user is not logged in, store token in session and redirect to login
            if (!Auth::check()) {
                session()->put('pending_invitation', $token);
                \Illuminate\Support\Facades\Log::info('User not logged in, storing token in session and redirecting');
                return redirect()->route('login')
                    ->with('info', 'Please log in to accept the invitation.');
            }

            // If user is logged in but email doesn't match, show error
            if (Auth::user()->email !== $invitation->email) {
                \Illuminate\Support\Facades\Log::warning('User email mismatch', [
                    'user_email' => Auth::user()->email,
                    'invitation_email' => $invitation->email,
                ]);
                return back()->with('error', 'This invitation is not for your account.');
            }

            // Process the invitation acceptance
            $this->processInvitationAcceptance($invitation);
            \Illuminate\Support\Facades\Log::info('Invitation accepted successfully');

            // Clear any existing intended URL in the session
            session()->forget('url.intended');

            // Force direct redirect to /admin without using intended()
            return redirect('/admin')
                ->with('success', 'You have successfully joined the team.');
        } catch (\Exception $e) {
            // Log error for debugging
            \Illuminate\Support\Facades\Log::error('Invitation acceptance error: ' . $e->getMessage(), [
                'token' => $token,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('login')
                ->with('error', 'The invitation link is invalid or has expired.');
        }
    }

    /**
     * Process the invitation acceptance.
     */
    protected function processInvitationAcceptance(TeamInvitation $invitation)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Update invitation status
        $invitation->update([
            'accepted_at' => now(),
        ]);

        // Attach user to team
        $invitation->team->users()->attach($user->id);

        // Assign role
        $user->assignRole($invitation->role);
    }

    public function login(Request $request)
    {
        $invitation = TeamInvitation::where('token', $request->token)
            ->whereNull('accepted_at')
            ->whereNull('rejected_at')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', Password::defaults()],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Process the invitation acceptance
            $this->processInvitationAcceptance($invitation);

            // Clear any existing intended URL in the session
            session()->forget('url.intended');

            // Use intended() with admin path as default
            return redirect()->intended('/admin')
                ->with('success', 'You have successfully joined the team.');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Public method to process invitation acceptance for external controllers.
     */
    public function acceptInvitation(TeamInvitation $invitation, User $user = null)
    {
        // If no user is provided, use the authenticated user
        if (!$user) {
            $user = Auth::user();
        }

        if (!$user) {
            throw new \Exception('No user provided or authenticated to accept invitation.');
        }

        // Update invitation status
        $invitation->update([
            'accepted_at' => now(),
        ]);

        // Attach user to team
        $invitation->team->users()->attach($user->id);

        // Assign role
        $user->assignRole($invitation->role);

        // Force direct redirect to /admin
        return redirect('/admin')
            ->with('success', 'You have successfully joined the team.');
    }
}
