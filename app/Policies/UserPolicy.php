<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy extends ModelPolicy
{
    /**
     * Perform pre-authorization checks to determine if the user is a super admin.
     */
    public function before(User $user, string $ability): bool|null
    {
        // Super admins can do anything
        if ($user->hasRole('super-admin')) {
            return true;
        }
        
        return null; // Fall through to the normal permission checks
    }
    
    /**
     * Get the permission name specific to this model.
     */
    protected function getPermissionName(): string
    {
        return 'user';
    }
}
