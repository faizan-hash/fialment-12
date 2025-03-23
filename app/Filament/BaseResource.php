<?php

namespace App\Filament;

use Filament\Resources\Resource;
use Illuminate\Support\Str;

abstract class BaseResource extends Resource
{
    /**
     * By default, all resources will use their model's policy.
     * We don't need to explicitly define canView, canEdit, etc. because
     * Filament will automatically check with the model's policy.
     * 
     * The policy methods (view, viewAny, create, update, delete) are 
     * automatically checked by Filament against the current user.
     * 
     * This eliminates hardcoded permission checks in each resource.
     */
    
    /**
     * When extending this class, you can override this if needed
     * or continue to use the default behavior.
     */
    public static function canCreate(): bool
    {
        $modelClass = static::getModel();
        $policy = app("App\\Policies\\" . class_basename($modelClass) . "Policy");
        
        return $policy->create(auth()->user());
    }
    
    /**
     * Control navigation visibility based on policy viewAny permission
     */
    public static function shouldRegisterNavigation(): bool
    {
        if (!auth()->check()) {
            return false;
        }
        
        $modelClass = static::getModel();
        $policy = app("App\\Policies\\" . class_basename($modelClass) . "Policy");
        
        return $policy->viewAny(auth()->user());
    }
    
    /**
     * Get the pluralized, kebab-cased model name for use with permissions.
     * You can override this method in specific resources if needed.
     */
    protected static function getPermissionName(): string
    {
        $modelClass = static::getModel();
        $modelBaseName = class_basename($modelClass);
        
        return Str::kebab(Str::plural($modelBaseName));
    }
} 