<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class GenerateModelPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-model-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate permissions for all models';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $models = $this->getModels();
        $this->info('Generating permissions for models: ' . implode(', ', $models));

        $actions = [
            'view any',
            'view',
            'create',
            'update',
            'delete',
            'restore',
            'force delete'
        ];

        $count = 0;

        foreach ($models as $model) {
            foreach ($actions as $action) {
                $permissionName = "{$action} {$model}";
                Permission::firstOrCreate(['name' => $permissionName]);
                $count++;
            }
        }

        $this->info("Created {$count} permissions successfully!");
    }

    /**
     * Get all model names in lowercase for permission generation.
     *
     * @return array
     */
    protected function getModels(): array
    {
        $models = [];
        $modelFiles = File::files(app_path('Models'));

        foreach ($modelFiles as $file) {
            $className = $file->getFilenameWithoutExtension();
            if ($className !== 'User') { // Skip User model
                $models[] = Str::kebab(Str::plural(strtolower($className)));
            }
        }

        return $models;
    }
}
