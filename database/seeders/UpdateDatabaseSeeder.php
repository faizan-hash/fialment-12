<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpdateDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds to update permissions for all roles.
     */
    public function run(): void
    {
        // Run all permission seeders in order
        $this->call([
            // First ensure all permissions exist for all models
            AllModelsPermissionsSeeder::class,
            
            // Then assign permissions to each role
            AssignPermissionsToAdminSeeder::class, // Admin gets all permissions
            AssignPermissionsToStudentSeeder::class,
            AssignPermissionsToMentorSeeder::class,
            AssignPermissionsToCoachSeeder::class,
        ]);
        
        $this->command->info('All roles and permissions updated successfully!');
    }
}
