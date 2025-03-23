<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class FeedbackPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Feedback permissions (used by FeedbackPolicy)
        $permissions = [
            'view any feedback',
            'view feedback',
            'create feedback',
            'update feedback',
            'delete feedback',
            'restore feedback',
            'force delete feedback'
        ];
        
        // Create all feedback permissions
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
