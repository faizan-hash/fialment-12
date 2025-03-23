<?php

namespace App\Policies;

use App\Models\SkillArea;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SkillAreaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All users can view skill areas
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SkillArea $skillArea): bool
    {
        return true; // All users can view individual skill areas
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin'); // Only admins can create skill areas
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SkillArea $skillArea): bool
    {
        return $user->hasRole('admin'); // Only admins can update skill areas
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SkillArea $skillArea): bool
    {
        return $user->hasRole('admin'); // Only admins can delete skill areas
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SkillArea $skillArea): bool
    {
        return $user->hasRole('admin'); // Only admins can restore skill areas
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SkillArea $skillArea): bool
    {
        return $user->hasRole('admin'); // Only admins can force delete skill areas
    }
} 