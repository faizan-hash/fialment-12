<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Team Invitation Route
Route::get('/join-team/{token}', [\App\Http\Controllers\TeamInvitationController::class, 'accept'])
    ->name('team.invitation.accept');

require __DIR__.'/auth.php';
