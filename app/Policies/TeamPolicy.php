<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

class TeamPolicy extends ModelPolicy
{
    /**
     * Get the permission name specific to this model.
     */
    protected function getPermissionName(): string
    {
        return 'team';
    }
    
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Model $model): bool
    {
        // First check if user has the specific permission
        if ($user->hasPermissionTo('update team')) {
            return true;
        }
        
        // Fallback to edit team permission if update isn't available
        if ($user->hasPermissionTo('edit team')) {
            return true;
        }
        
        // Admin can always update
        if ($user->hasRole('admin')) {
            return true;
        }
        
        return false;
    }
}
