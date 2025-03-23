<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AssignPermissionsToCoachSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get personal coach role
        $coachRole = Role::where('name', 'personal_coach')->first();
        
        if (!$coachRole) {
            $this->command->error('Personal Coach role not found!');
            return;
        }
        
        // Resources that should be visible to coaches in the sidebar
        $visibleResources = [
            'teams',
            'skills',
            'practices',
            'feedback',
            'users', // Coaches need to see users to provide feedback
            'coach-students', // Coaches need to see their assigned students
        ];
        
        // Specific permissions that coaches need
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
            'view any users',
            'view users',
            'view any coach-students',
            'view coach-students',
            
            // Feedback-specific permissions
            'create feedback',
            'update feedback', // To edit their own feedback
            
            // Cannot create/update/delete teams, skills, practices
        ];
        
        // Process view any permissions for navigation
        foreach ($visibleResources as $resource) {
            // The permission name that controls sidebar visibility
            $viewAnyPermission = "view any {$resource}";
            
            // Check if permission exists
            $permission = Permission::where('name', $viewAnyPermission)->first();
            
            if ($permission) {
                // Give permission to coach role if it doesn't already have it
                if (!$coachRole->hasPermissionTo($viewAnyPermission)) {
                    $coachRole->givePermissionTo($viewAnyPermission);
                    $this->command->info("Gave permission '{$viewAnyPermission}' to coach role");
                } else {
                    $this->command->info("Coach role already has permission '{$viewAnyPermission}'");
                }
            } else {
                // Create permission if it doesn't exist
                $permission = Permission::create(['name' => $viewAnyPermission, 'guard_name' => 'web']);
                $coachRole->givePermissionTo($permission);
                $this->command->info("Created and assigned permission '{$viewAnyPermission}' to coach role");
            }
        }
        
        // Process specific permissions
        foreach ($specificPermissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            
            if ($permission) {
                if (!$coachRole->hasPermissionTo($permissionName)) {
                    $coachRole->givePermissionTo($permissionName);
                    $this->command->info("Gave permission '{$permissionName}' to coach role");
                } else {
                    $this->command->info("Coach role already has permission '{$permissionName}'");
                }
            } else {
                $permission = Permission::create(['name' => $permissionName, 'guard_name' => 'web']);
                $coachRole->givePermissionTo($permission);
                $this->command->info("Created and assigned permission '{$permissionName}' to coach role");
            }
        }
        
        $this->command->info('Personal Coach permissions updated successfully!');
    }
}
