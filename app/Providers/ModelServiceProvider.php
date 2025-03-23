<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class ModelServiceProvider extends ServiceProvider
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
        // Using a closure-based approach to avoid issues with Model instantiation
        $this->app->resolving('db', function ($db) {
            // Add the methods to the Model class
            $resolvePolicy = function () {
                $modelClass = get_class($this);
                $modelName = class_basename($modelClass);
                $policyClass = "App\\Policies\\{$modelName}Policy";
                
                if (class_exists($policyClass)) {
                    return app($policyClass);
                }
                
                // Fall back to a generic policy if model-specific one doesn't exist
                return app(\App\Policies\ModelPolicy::class);
            };
            
            Model::macro('resolvePolicy', $resolvePolicy);
        });
    }
}
