<?php

namespace App\Policies;

use App\Models\Practice;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

class PracticePolicy extends ModelPolicy
{
    /**
     * Get the permission name specific to this model.
     */
    protected function getPermissionName(): string
    {
        return 'practices';
    }
    
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Model $model): bool
    {
        // First check if user has the specific permission
        if ($user->hasPermissionTo('update practices')) {
            return true;
        }
        
        // Fallback to edit practices permission if update isn't available
        if ($user->hasPermissionTo('edit practices')) {
            return true;
        }
        
        // Admin can always update
        if ($user->hasRole('admin')) {
            return true;
        }
        
        return false;
    }
}