<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;

class GoogleController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }
    
    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            $user = User::where('email', $googleUser->email)->first();
            
            if (!$user) {
                // Create a new user
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => Hash::make(Str::random(10)), // Random password
                ]);
                
                // Assign the default student role
                $user->assignRole('student');
            }
            
            // Login the user
            Auth::login($user);
            
            // Check if there's a pending invitation in the session
            $pendingInvitation = session('pending_invitation');
            
            \Illuminate\Support\Facades\Log::info('Google callback - checking pending invitation', [
                'has_pending_invitation' => !empty($pendingInvitation),
                'token' => $pendingInvitation,
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);
            
            // Directly process the invitation here instead of relying on middleware
            if ($pendingInvitation) {
                session()->forget('pending_invitation');
                
                $invitation = \App\Models\TeamInvitation::where('token', $pendingInvitation)
                    ->whereNull('accepted_at')
                    ->whereNull('rejected_at')
                    ->first();
                
                if ($invitation && $invitation->email === $user->email) {
                    \Illuminate\Support\Facades\Log::info('Google callback - processing invitation', [
                        'invitation_id' => $invitation->id,
                        'invitation_email' => $invitation->email,
                        'user_email' => $user->email
                    ]);
                    
                    $controller = new \App\Http\Controllers\TeamInvitationController();
                    return $controller->processInvitationAcceptance($invitation, $user);
                }
            }
            
            return redirect()->intended(route('filament.admin.pages.dashboard'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google authentication error', [
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('login')
                ->with('error', 'Google authentication failed: ' . $e->getMessage());
        }
    }
}
