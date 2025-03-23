<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class CreatePermissions extends Command
{
    protected $signature = 'app:create-permissions';
    protected $description = 'Create all required permissions for the policy-based system';

    public function handle()
    {
        $modelNames = [
            'teams',
            'feedback',
            'skills',
            'practices',
            'users',
            'team-invitations',
            'coach-students',
            'coach-assignments',
            'permissions',
            'roles',
            'permission-groups'
        ];

        $actions = [
            'view any',
            'view',
            'create',
            'update',
            'delete',
            'restore',
            'force delete'
        ];

        $count = 0;

        foreach ($modelNames as $model) {
            foreach ($actions as $action) {
                $permissionName = "{$action} {$model}";
                
                if (!Permission::where('name', $permissionName)->exists()) {
                    Permission::create(['name' => $permissionName, 'guard_name' => 'web']);
                    $this->info("Created permission: {$permissionName}");
                    $count++;
                } else {
                    $this->info("Permission already exists: {$permissionName}");
                }
            }
        }

        $this->info("Created {$count} permissions");
    }
} 