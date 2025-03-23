<?php

namespace App\Providers;

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\ProcessPendingInvitation;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class HttpKernelServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('role', CheckRole::class);
        $router->aliasMiddleware('permission', CheckPermission::class);
        $router->aliasMiddleware('process.invitation', ProcessPendingInvitation::class);
        
        // Add the middleware to web group to process invitations after login
        $router->pushMiddlewareToGroup('web', ProcessPendingInvitation::class);
    }
}
