<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TestUsersWithRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test subject mentor user
        $mentorUser = User::create([
            'name' => 'Test Subject Mentor',
            'email' => 'mentor@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        
        // Assign subject_mentor role
        $mentorRole = Role::where('name', 'subject_mentor')->first();
        if ($mentorRole) {
            $mentorUser->assignRole($mentorRole);
            $this->command->info('Created user with subject_mentor role');
        } else {
            $this->command->error('subject_mentor role not found!');
        }
        
        // Create a test personal coach user
        $coachUser = User::create([
            'name' => 'Test Personal Coach',
            'email' => 'coach@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        
        // Assign personal_coach role
        $coachRole = Role::where('name', 'personal_coach')->first();
        if ($coachRole) {
            $coachUser->assignRole($coachRole);
            $this->command->info('Created user with personal_coach role');
        } else {
            $this->command->error('personal_coach role not found!');
        }
        
        $this->command->info('Test users created successfully!');
    }
}
