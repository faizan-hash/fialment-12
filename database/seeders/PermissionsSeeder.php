<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Get a list of all model names
        $modelDirs = File::files(app_path('Models'));
        $models = [];
        
        foreach ($modelDirs as $file) {
            $className = $file->getFilenameWithoutExtension();
            // Skip User as it's handled separately
            if ($className !== 'User') {
                $models[] = Str::kebab(Str::plural(strtolower($className)));
            }
        }
        
        // Add User permissions specifically
        $models[] = 'users';
        
        // Actions that can be performed on each model
        $actions = [
            'view any',
            'view',
            'create',
            'update',
            'delete',
            'restore',
            'force delete'
        ];

        // Create all permissions
        foreach ($models as $model) {
            foreach ($actions as $action) {
                $permissionName = "{$action} {$model}";
                
                // Check if permission already exists
                if (!Permission::where('name', $permissionName)->exists()) {
                    Permission::create(['name' => $permissionName]);
                    $this->command->info("Created permission: {$permissionName}");
                } else {
                    $this->command->info("Permission already exists: {$permissionName}");
                }
            }
        }
    }
} 