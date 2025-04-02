<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            // Check if the email already exists
            $emailExists = DB::table('users')->where('email', $request->email)->exists();
            if ($emailExists) {
                return back()->withErrors(['email' => 'This email is already in use.'])
                    ->withInput();
            }

            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => User::ROLE_STUDENT, // Default role
            ]);

            // Assign the student role to the new user
            $studentRole = Role::where('name', 'student')->first();
            if ($studentRole) {
                $user->assignRole($studentRole);
            }

            event(new Registered($user));

            Auth::login($user);

            // Display success notification
            Notification::make()
                ->success()
                ->title('Registration Successful')
                ->body('Your account has been created successfully.')
                ->send();

            return redirect(route('dashboard', absolute: false));
        } catch (\Exception $e) {
            // Display error notification
            Notification::make()
                ->danger()
                ->title('Registration Failed')
                ->body('There was an error creating your account: ' . $e->getMessage())
                ->send();

            return back()->withErrors(['email' => 'Registration failed. Please try again.'])
                ->withInput();
        }
    }
}
