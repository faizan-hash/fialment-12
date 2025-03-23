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
        // The following seeders are no longer needed as their functionality is 
        // included in the RolesAndPermissionsSeeder:
        // - All individual permission seeders
        // - TestUsersWithRolesSeeder (replaced by DummyDataSeeder)
        // - UpdateDatabaseSeeder
        $this->call([
            RolesAndPermissionsSeeder::class, // Creates all permissions and roles
            AdminUserSeeder::class,           // Creates admin user
            SkillsAndPracticesSeeder::class,  // Creates basic skills and practices
            DummyDataSeeder::class,           // Creates test users, teams, etc.
        ]);
    }
}
