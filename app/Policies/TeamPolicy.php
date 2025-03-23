<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

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
        try {
            if ($user->hasPermissionTo('update team')) {
                return true;
            }
        } catch (PermissionDoesNotExist $e) {
            // Permission doesn't exist, try the next one
        }
        
        // Fallback to edit team permission if update isn't available
        try {
            if ($user->hasPermissionTo('edit team')) {
                return true;
            }
        } catch (PermissionDoesNotExist $e) {
            // Permission doesn't exist, use role-based check
        }
        
        // Admin can always update
        return $user->hasRole('admin');
    }
}
