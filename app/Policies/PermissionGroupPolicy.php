<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PermissionGroup;
use Illuminate\Auth\Access\Response;

class PermissionGroupPolicy extends ModelPolicy
{
    /**
     * Get the permission name specific to this model.
     */
    protected function getPermissionName(): string
    {
        return 'permission-groups';
    }
}
