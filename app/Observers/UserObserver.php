<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Sync the user with their role in the roles_model relationship
        if (!$user->hasAnyRole()) {
            // Get role from the 'role' attribute if it exists
            $role = $user->role;
            
            if ($role) {
                $user->assignRole($role);
            }
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Check if the role attribute was changed
        if ($user->isDirty('role')) {
            $role = $user->role;
            
            // Remove all current roles and assign the new one
            $user->syncRoles([$role]);
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Remove all roles when a user is deleted
        $user->syncRoles([]);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        // Remove all roles when a user is force deleted
        $user->syncRoles([]);
    }
}
