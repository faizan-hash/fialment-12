<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PracticePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Practice permissions (used by PracticePolicy) - using singular 'practice'
        $permissions = [
            'view any practices',
            'view practices',
            'create practices',
            'update practices',
            'delete practices',
            'restore practices',
            'force delete practices'
        ];
        
        // Create all practice permissions
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
