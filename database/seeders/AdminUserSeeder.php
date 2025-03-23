<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin user already exists
        $existingAdmin = User::where('email', 'admin@example.com')->first();

        if (!$existingAdmin) {
            // Create admin user if it doesn't exist
            $admin = User::create([
                'name' => 'Project Advisor',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]);
            
            $admin->assignRole('admin');
        } else {
            // Make sure existing admin has the admin role
            $existingAdmin->assignRole('admin');
        }
    }
}
