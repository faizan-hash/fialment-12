<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AllModelsPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // All policy permission names (matching each policy's getPermissionName())
        $permissionSets = [
            'user' => 'User',
            'team' => 'Team',
            'team-invitations' => 'TeamInvitation',
            'skills' => 'Skill',
            'practices' => 'Practice',
            'feedback' => 'Feedback',
            'coach-students' => 'CoachStudent',
            'roles' => 'Role',
            'permissions' => 'Permission'
        ];
        
        // Actions that can be performed on each model
        $actions = [
            'view any',
            'view',
            'create',
            'update',
            'delete',
            'restore',
            'force delete'
        ];

        // Create permissions for each model
        foreach ($permissionSets as $permissionName => $modelName) {
            $this->command->info("Creating permissions for {$modelName}");
            
            foreach ($actions as $action) {
                $fullPermissionName = "{$action} {$permissionName}";
                
                // Check if permission already exists
                if (!Permission::where('name', $fullPermissionName)->exists()) {
                    Permission::create(['name' => $fullPermissionName]);
                    $this->command->info("  Created permission: {$fullPermissionName}");
                } else {
                    $this->command->info("  Permission already exists: {$fullPermissionName}");
                }
            }
        }
        
        // Assign all permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        
        if ($adminRole) {
            $permissions = Permission::all();
            $adminRole->syncPermissions($permissions);
            $this->command->info("All permissions assigned to admin role (" . $permissions->count() . " total)");
        } else {
            $this->command->error("Admin role not found!");
        }
    }
}
