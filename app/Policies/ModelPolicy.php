<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class ModelPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        try {
            if ($user->hasPermissionTo('view any ' . $this->getPermissionName())) {
                return true;
            }
        } catch (PermissionDoesNotExist $e) {
            // Permission doesn't exist, use fallback
        }
        
        // Fallback to role-based check
        return $this->hasAdminRole($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Model $model): bool
    {
        try {
            if ($user->hasPermissionTo('view ' . $this->getPermissionName())) {
                return true;
            }
        } catch (PermissionDoesNotExist $e) {
            // Permission doesn't exist, use fallback
        }
        
        // Fallback to role-based check
        return $this->hasAdminRole($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        try {
            if ($user->hasPermissionTo('create ' . $this->getPermissionName())) {
                return true;
            }
        } catch (PermissionDoesNotExist $e) {
            // Permission doesn't exist, use fallback
        }
        
        // Fallback to role-based check
        return $this->hasAdminRole($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Model $model): bool
    {
        try {
            if ($user->hasPermissionTo('update ' . $this->getPermissionName())) {
                return true;
            }
        } catch (PermissionDoesNotExist $e) {
            // Fall back to edit permission
            try {
                if ($user->hasPermissionTo('edit ' . $this->getPermissionName())) {
                    return true;
                }
            } catch (PermissionDoesNotExist $e) {
                // Both permissions don't exist, use fallback
            }
        }
        
        // Fallback to role-based check
        return $this->hasAdminRole($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Model $model): bool
    {
        try {
            if ($user->hasPermissionTo('delete ' . $this->getPermissionName())) {
                return true;
            }
        } catch (PermissionDoesNotExist $e) {
            // Permission doesn't exist, use fallback
        }
        
        // Fallback to role-based check
        return $this->hasAdminRole($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Model $model): bool
    {
        try {
            if ($user->hasPermissionTo('restore ' . $this->getPermissionName())) {
                return true;
            }
        } catch (PermissionDoesNotExist $e) {
            // Permission doesn't exist, use fallback
        }
        
        // Fallback to role-based check
        return $this->hasAdminRole($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Model $model): bool
    {
        try {
            if ($user->hasPermissionTo('force delete ' . $this->getPermissionName())) {
                return true;
            }
        } catch (PermissionDoesNotExist $e) {
            // Permission doesn't exist, use fallback
        }
        
        // Fallback to role-based check
        return $this->hasAdminRole($user);
    }
    
    /**
     * Get the permission name derived from the class name.
     */
    protected function getPermissionName(): string
    {
        // Default implementation - should be overridden by child classes
        return 'model';
    }
    
    /**
     * Check if the user has an admin role.
     */
    protected function hasAdminRole(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
