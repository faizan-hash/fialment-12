<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

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
        return $user->hasPermissionTo('view any ' . $this->getPermissionName());
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Model $model): bool
    {
        return $user->hasPermissionTo('view ' . $this->getPermissionName());
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create ' . $this->getPermissionName());
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Model $model): bool
    {
        return $user->hasPermissionTo('update ' . $this->getPermissionName());
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Model $model): bool
    {
        return $user->hasPermissionTo('delete ' . $this->getPermissionName());
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Model $model): bool
    {
        return $user->hasPermissionTo('restore ' . $this->getPermissionName());
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Model $model): bool
    {
        return $user->hasPermissionTo('force delete ' . $this->getPermissionName());
    }
    
    /**
     * Get the permission name derived from the class name.
     */
    protected function getPermissionName(): string
    {
        // Default implementation - should be overridden by child classes
        return 'model';
    }
}
