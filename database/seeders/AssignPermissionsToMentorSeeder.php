<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AssignPermissionsToMentorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get subject mentor role
        $mentorRole = Role::where('name', 'subject_mentor')->first();
        
        if (!$mentorRole) {
            $this->command->error('Subject Mentor role not found!');
            return;
        }
        
        // Resources that should be visible to mentors in the sidebar
        $visibleResources = [
            'teams',
            'skills',
            'practices',
            'feedback',
            'users', // Subject mentors need to see users to provide feedback
        ];
        
        // Specific permissions that mentors need
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
                // Give permission to mentor role if it doesn't already have it
                if (!$mentorRole->hasPermissionTo($viewAnyPermission)) {
                    $mentorRole->givePermissionTo($viewAnyPermission);
                    $this->command->info("Gave permission '{$viewAnyPermission}' to mentor role");
                } else {
                    $this->command->info("Mentor role already has permission '{$viewAnyPermission}'");
                }
            } else {
                // Create permission if it doesn't exist
                $permission = Permission::create(['name' => $viewAnyPermission, 'guard_name' => 'web']);
                $mentorRole->givePermissionTo($permission);
                $this->command->info("Created and assigned permission '{$viewAnyPermission}' to mentor role");
            }
        }
        
        // Process specific permissions
        foreach ($specificPermissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            
            if ($permission) {
                if (!$mentorRole->hasPermissionTo($permissionName)) {
                    $mentorRole->givePermissionTo($permissionName);
                    $this->command->info("Gave permission '{$permissionName}' to mentor role");
                } else {
                    $this->command->info("Mentor role already has permission '{$permissionName}'");
                }
            } else {
                $permission = Permission::create(['name' => $permissionName, 'guard_name' => 'web']);
                $mentorRole->givePermissionTo($permission);
                $this->command->info("Created and assigned permission '{$permissionName}' to mentor role");
            }
        }
        
        $this->command->info('Subject Mentor permissions updated successfully!');
    }
}
