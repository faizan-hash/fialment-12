<?php

namespace App\Policies;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

class SkillPolicy extends ModelPolicy
{
    /**
     * Get the permission name specific to this model.
     */
    protected function getPermissionName(): string
    {
        return 'skills';
    }
    
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Model $model): bool
    {
        // First check if user has the specific permission
        if ($user->hasPermissionTo('update skills')) {
            return true;
        }
        
        // Fallback to edit skills permission if update isn't available
        if ($user->hasPermissionTo('edit skills')) {
            return true;
        }
        
        // Admin can always update
        if ($user->hasRole('admin')) {
            return true;
        }
        
        return false;
    }
}