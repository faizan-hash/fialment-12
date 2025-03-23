<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Only call essential seeders
        // 
        // NOTE: The following seeders should be removed from the codebase as they are no longer needed:
        // - AllModelsPermissionsSeeder.php
        // - AssignPermissionsToAdminSeeder.php
        // - AssignPermissionsToCoachSeeder.php
        // - AssignPermissionsToMentorSeeder.php
        // - AssignPermissionsToStudentSeeder.php
        // - CoachStudentPermissionsSeeder.php
        // - FeedbackPermissionsSeeder.php
        // - PermissionsSeeder.php
        // - PracticePermissionsSeeder.php
        // - SkillPermissionsSeeder.php
        // - TeamInvitationPermissionsSeeder.php
        // - TeamPermissionsSeeder.php
        // - TestUsersWithRolesSeeder.php
        // - UpdateDatabaseSeeder.php
        //
        // Their functionality is included in RolesAndPermissionsSeeder.
        
        $this->call([
            RolesAndPermissionsSeeder::class, // Creates all permissions and roles
            AdminUserSeeder::class,           // Creates admin user
            SkillAreasSeeder::class,
            SkillsAndPracticesSeeder::class,  // Creates basic skills and practices
            DummyDataSeeder::class,           // Creates test users, teams, etc.
        ]);
    }
}
