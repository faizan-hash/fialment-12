<?php

namespace App\Policies;

use App\Models\CoachStudent;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CoachStudentPolicy extends ModelPolicy
{
    /**
     * Get the permission name specific to this model.
     */
    protected function getPermissionName(): string
    {
        return 'coach-students';
    }
}