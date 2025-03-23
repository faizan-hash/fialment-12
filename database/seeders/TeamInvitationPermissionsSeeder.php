<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class TeamInvitationPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // TeamInvitation permissions (used by TeamInvitationPolicy)
        $permissions = [
            'view any team-invitations',
            'view team-invitations',
            'create team-invitations',
            'update team-invitations',
            'delete team-invitations',
            'restore team-invitations',
            'force delete team-invitations'
        ];
        
        // Create all team invitation permissions
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
