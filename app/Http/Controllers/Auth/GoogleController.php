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
use Filament\Notifications\Notification;

class GoogleController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGoogle()
    {
        // Log the redirect URI for debugging
        \Illuminate\Support\Facades\Log::info('Google OAuth redirect initiated', [
            'redirect_uri' => config('services.google.redirect'),
            'app_url' => config('app.url')
        ]);

        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleGoogleCallback()
    {
        try {
            // Get the OAuth response from the state cookie for enhanced debugging
            $state = request()->input('state');
            $code = request()->input('code');

            // Log the authorization data for debugging
            \Illuminate\Support\Facades\Log::info('Google OAuth attempt', [
                'state' => $state,
                'code_present' => !empty($code),
                'request_uri' => request()->getRequestUri(),
                'redirect_uri' => config('services.google.redirect')
            ]);

            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            if (empty($googleUser->email)) {
                throw new \Exception("Google did not provide an email address");
            }

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

                // Display success notification
                Notification::make()
                    ->success()
                    ->title('Registration Successful')
                    ->body('Your account has been created successfully.')
                    ->send();
            } else {
                // Update google_id if not already set
                if (empty($user->google_id)) {
                    $user->update(['google_id' => $googleUser->id]);
                }
            }

            // Login the user
            Auth::login($user);

            // Check if there's a pending invitation in the session
            $pendingInvitation = session('pending_invitation');

            // Process the invitation if it exists
            if ($pendingInvitation) {
                session()->forget('pending_invitation');

                try {
                    $invitation = TeamInvitation::where('token', $pendingInvitation)
                        ->whereNull('accepted_at')
                        ->whereNull('rejected_at')
                        ->first();

                    if ($invitation && $invitation->email === $user->email) {
                        $controller = new TeamInvitationController();

                        // Check if user is already part of the team to prevent duplicate entry
                        $alreadyInTeam = \DB::table('team_user')
                            ->where('team_id', $invitation->team_id)
                            ->where('user_id', $user->id)
                            ->exists();

                        if (!$alreadyInTeam) {
                            return $controller->acceptInvitation($invitation, $user);
                        } else {
                            // If already in team, just mark invitation as accepted
                            $invitation->update(['accepted_at' => now()]);

                            // Ensure user has the correct role
                            if (!$user->hasRole($invitation->role)) {
                                $user->assignRole($invitation->role);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Error processing invitation after Google login', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            // Clear any existing intended URL
            session()->forget('url.intended');

            // Direct redirect to admin panel without intended()
            return redirect('/admin');
        } catch (\Exception $e) {
            // Enhanced error logging
            \Illuminate\Support\Facades\Log::error('Google authentication error', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'state' => request()->input('state'),
                'code_present' => !empty(request()->input('code')),
                'redirect_uri' => config('services.google.redirect'),
                'app_url' => config('app.url'),
                'request_uri' => request()->getRequestUri()
            ]);

            // Determine a more specific error message based on the exception
            $errorMessage = 'There was an error authenticating with Google. Please try again later.';

            if (strpos($e->getMessage(), 'Integrity constraint violation') !== false) {
                $errorMessage = 'Your account already exists in our system. Please try logging in directly.';
            } elseif (strpos($e->getMessage(), 'invalid_grant') !== false) {
                $errorMessage = 'The authentication request expired or was revoked. Please try again.';
            } elseif (strpos($e->getMessage(), 'redirect_uri_mismatch') !== false) {
                $errorMessage = 'There was a configuration error. Please contact support.';
            }

            // Display error notification
            Notification::make()
                ->danger()
                ->title('Google Authentication Failed')
                ->body($errorMessage)
                ->send();

            return redirect()->route('login')
                ->with('error', $errorMessage);
        }
    }
}
