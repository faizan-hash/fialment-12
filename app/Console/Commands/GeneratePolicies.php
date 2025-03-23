<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;

class GeneratePolicies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-policies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate policies for all models';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $models = $this->getModels();
        $count = 0;

        $this->info('Generating policies for models: ' . implode(', ', $models));

        foreach ($models as $model) {
            if (!File::exists(app_path("Policies/{$model}Policy.php"))) {
                $this->info("Generating policy for {$model}...");
                Artisan::call('make:policy', [
                    'name' => "{$model}Policy",
                    '--model' => $model,
                ]);
                
                // Update the generated policy to extend ModelPolicy
                $this->updatePolicy($model);
                $count++;
            } else {
                $this->info("Policy for {$model} already exists, skipping.");
            }
        }

        $this->info("Generated {$count} policies successfully!");
    }

    /**
     * Get all model class names
     *
     * @return array
     */
    protected function getModels(): array
    {
        $models = [];
        $modelFiles = File::files(app_path('Models'));

        foreach ($modelFiles as $file) {
            $models[] = $file->getFilenameWithoutExtension();
        }

        return $models;
    }

    /**
     * Update the generated policy to extend ModelPolicy
     */
    protected function updatePolicy(string $model): void
    {
        $policyPath = app_path("Policies/{$model}Policy.php");
        
        if (File::exists($policyPath)) {
            $policyContent = File::get($policyPath);
            
            // Replace the class declaration to extend ModelPolicy
            $policyContent = preg_replace(
                "/class {$model}Policy(\s+)\{/",
                "class {$model}Policy extends ModelPolicy\n{\n    /**\n     * Get the permission name specific to this model.\n     */\n    protected function getPermissionName(): string\n    {\n        return '" . Str::kebab($model) . "';\n    }",
                $policyContent
            );
            
            // Remove all the standard methods that are defined in ModelPolicy
            $policyContent = preg_replace(
                "/public function (viewAny|view|create|update|delete|restore|forceDelete).*?}\n}/s",
                "}",
                $policyContent
            );
            
            File::put($policyPath, $policyContent);
            $this->info("Updated {$model}Policy to extend ModelPolicy.");
        }
    }
}
