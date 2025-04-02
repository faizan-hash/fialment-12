<?php

namespace App\Providers;

use App\Models\Feedback;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Observers\FeedbackObserver;
use App\Observers\TeamInvitationObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register the User observer
        User::observe(UserObserver::class);

        // Register the TeamInvitation observer
        TeamInvitation::observe(TeamInvitationObserver::class);

        // Register the Feedback observer
        Feedback::observe(FeedbackObserver::class);
    }
}
