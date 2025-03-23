<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GeneratePoliciesSimple extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-policies-simple';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate simple policies for all models';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // List of model names to create policies for
        $models = [
            'Feedback',
            'Team',
            'TeamInvitation',
            'Skill',
            'Practice',
            'User',
            'CoachStudent'
        ];
        
        $this->info('Generating policies for models: ' . implode(', ', $models));
        
        $count = 0;
        foreach ($models as $model) {
            $policyPath = app_path("Policies/{$model}Policy.php");
            
            if (!File::exists($policyPath)) {
                $this->createPolicy($model);
                $count++;
            } else {
                $this->info("Policy {$model}Policy already exists. Skipping.");
            }
        }
        
        $this->info("Created {$count} policies.");
    }
    
    /**
     * Create a policy file for the given model
     */
    protected function createPolicy(string $model): void
    {
        $policyPath = app_path("Policies/{$model}Policy.php");
        $permissionName = Str::kebab(Str::plural($model));
        
        $stub = <<<EOT
<?php

namespace App\Policies;

use App\Models\\{$model};
use App\Models\User;
use Illuminate\Auth\Access\Response;

class {$model}Policy extends ModelPolicy
{
    /**
     * Get the permission name specific to this model.
     */
    protected function getPermissionName(): string
    {
        return '{$permissionName}';
    }
}
EOT;

        File::put($policyPath, $stub);
        $this->info("Created policy {$model}Policy.");
    }
}
