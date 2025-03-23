<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AssignPermissionsToAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin role
        $adminRole = Role::where('name', 'admin')->first();
        
        if (!$adminRole) {
            $this->command->error('Admin role not found!');
            return;
        }
        
        // Get all permissions and assign to admin
        $permissions = Permission::all();
        
        $adminRole->syncPermissions($permissions);
        
        $this->command->info('All permissions (' . $permissions->count() . ') have been assigned to the admin role.');
    }
}
