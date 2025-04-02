<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('filament.admin.pages.dashboard');
    }
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return redirect('/admin');
    })->name('dashboard');

    Route::view('profile', 'profile')->name('profile');
});

// Team Invitation Route - Accessible without authentication and at root level
Route::get('join-team/{token}', [\App\Http\Controllers\TeamInvitationController::class, 'accept'])
    ->name('team.invitation.accept');

// Also register the same route without the leading slash to catch potential URL variations
Route::get('join-team/{token}', [\App\Http\Controllers\TeamInvitationController::class, 'accept']);

require __DIR__.'/auth.php';
