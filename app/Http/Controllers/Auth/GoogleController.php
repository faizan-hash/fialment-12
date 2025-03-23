<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TeamInvitationController;
use App\Models\TeamInvitation;
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
            
            // Process the invitation if it exists
            if ($pendingInvitation) {
                session()->forget('pending_invitation');
                
                $invitation = TeamInvitation::where('token', $pendingInvitation)
                    ->whereNull('accepted_at')
                    ->whereNull('rejected_at')
                    ->first();
                
                if ($invitation && $invitation->email === $user->email) {
                    $controller = new TeamInvitationController();
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
