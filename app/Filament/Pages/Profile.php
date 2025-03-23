<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'My Profile';
    protected static ?string $navigationGroup = 'Account';
    protected static ?int $navigationSort = 10;

    // Always show this page in navigation regardless of permissions
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check();
    }

    protected static string $view = 'filament.pages.profile';
    
    public ?array $userData = [];
    public ?string $password = null;
    public ?string $passwordConfirmation = null;
    
    public function mount(): void
    {
        $user = Auth::user();
        
        $this->userData = [
            'name' => $user->name,
            'email' => $user->email,
        ];
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Profile Information')
                    ->description('Update your account\'s profile information.')
                    ->schema([
                        TextInput::make('userData.name')
                            ->label('Name')
                            ->required(),
                        TextInput::make('userData.email')
                            ->label('Email')
                            ->email()
                            ->required(),
                    ]),
                
                Section::make('Update Password')
                    ->description('Ensure your account is using a long, random password to stay secure.')
                    ->schema([
                        TextInput::make('password')
                            ->label('New Password')
                            ->password()
                            ->rule(Password::default())
                            ->dehydrated(fn ($state): bool => filled($state))
                            ->same('passwordConfirmation'),
                        TextInput::make('passwordConfirmation')
                            ->label('Confirm Password')
                            ->password()
                            ->dehydrated(false),
                    ]),
            ]);
    }
    
    public function updateProfile(): void
    {
        $this->form->getState();
        
        $user = Auth::user();
        
        $user->update([
            'name' => $this->userData['name'],
            'email' => $this->userData['email'],
        ]);
        
        if ($this->password) {
            $user->update([
                'password' => Hash::make($this->password),
            ]);
        }
        
        Notification::make()
            ->title('Profile updated successfully')
            ->success()
            ->send();
    }
}
