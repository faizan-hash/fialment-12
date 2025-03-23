<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AssignPermissionsToStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get student role
        $studentRole = Role::where('name', 'student')->first();
        
        if (!$studentRole) {
            $this->command->error('Student role not found!');
            return;
        }
        
        // Resources that should be visible to students in the sidebar
        $visibleResources = [
            'teams',
            'skills',
            'practices',
            'feedback'
        ];
        
        // Specific permissions that students need
        $specificPermissions = [
            // View permissions
            'view any teams',
            'view teams',
            'view any skills',
            'view skills',
            'view any practices', 
            'view practices',
            'view any feedback',
            'view feedback',
            'view own feedback',
            
            // Feedback-specific permissions
            'create feedback',
            'update feedback', // To edit their own feedback
            
            // Cannot create/manage teams, skills, practices
        ];
        
        // Process view any permissions for navigation
        foreach ($visibleResources as $resource) {
            // The permission name that controls sidebar visibility
            $viewAnyPermission = "view any {$resource}";
            
            // Check if permission exists
            $permission = Permission::where('name', $viewAnyPermission)->first();
            
            if ($permission) {
                // Give permission to student role if it doesn't already have it
                if (!$studentRole->hasPermissionTo($viewAnyPermission)) {
                    $studentRole->givePermissionTo($viewAnyPermission);
                    $this->command->info("Gave permission '{$viewAnyPermission}' to student role");
                } else {
                    $this->command->info("Student role already has permission '{$viewAnyPermission}'");
                }
            } else {
                // Create permission if it doesn't exist
                $permission = Permission::create(['name' => $viewAnyPermission, 'guard_name' => 'web']);
                $studentRole->givePermissionTo($permission);
                $this->command->info("Created and assigned permission '{$viewAnyPermission}' to student role");
            }
        }
        
        // Process specific permissions
        foreach ($specificPermissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            
            if ($permission) {
                if (!$studentRole->hasPermissionTo($permissionName)) {
                    $studentRole->givePermissionTo($permissionName);
                    $this->command->info("Gave permission '{$permissionName}' to student role");
                } else {
                    $this->command->info("Student role already has permission '{$permissionName}'");
                }
            } else {
                $permission = Permission::create(['name' => $permissionName, 'guard_name' => 'web']);
                $studentRole->givePermissionTo($permission);
                $this->command->info("Created and assigned permission '{$permissionName}' to student role");
            }
        }
        
        $this->command->info('Student permissions updated successfully!');
    }
}
