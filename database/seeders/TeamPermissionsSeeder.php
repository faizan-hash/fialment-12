<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class TeamPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Team singular permissions (needed for policies)
        $teamPermissions = [
            'view any team',
            'view team',
            'create team',
            'update team',
            'delete team',
            'restore team',
            'force delete team'
        ];
        
        // Create all team permissions
        foreach ($teamPermissions as $permission) {
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
