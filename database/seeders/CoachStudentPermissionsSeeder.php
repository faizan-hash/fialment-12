<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class CoachStudentPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // CoachStudent permissions (used by CoachStudentPolicy with kebab-case)
        $permissions = [
            'view any coach-students',
            'view coach-students',
            'create coach-students',
            'update coach-students',
            'delete coach-students',
            'restore coach-students',
            'force delete coach-students'
        ];
        
        // Create all coach-students permissions
        foreach ($permissions as $permission) {
            // Check if permission already exists
            if (!Permission::where('name', $permission)->exists()) {
                Permission::create(['name' => $permission]);
                $this->command->info("Created permission: {$permission}");
            } else {
                $this->command->info("Permission already exists: {$permission}");
            }
        }
    }
}
