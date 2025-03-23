<?php

namespace Database\Seeders;

use App\Models\Practice;
use App\Models\Skill;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SkillsAndPracticesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Skill 1: Offering a Teammate Assistance
        $skill1 = Skill::create([
            'name' => 'Offering a Teammate Assistance',
            'description' => 'Identify opportunities to help teammates and provide effective assistance.',
        ]);

        Practice::create([
            'skill_id' => $skill1->id,
            'description' => 'Identify when a teammate is struggling and offer help proactively.',
            'order' => 1,
        ]);

        Practice::create([
            'skill_id' => $skill1->id,
            'description' => 'Share resources or knowledge that could assist the teammate.',
            'order' => 2,
        ]);

        Practice::create([
            'skill_id' => $skill1->id,
            'description' => 'Follow up to ensure the teammate has successfully resolved the issue.',
            'order' => 3,
        ]);

        // Skill 2: Identifying Bias(es) from a Particular Source
        $skill2 = Skill::create([
            'name' => 'Identifying Bias(es) from a Particular Source',
            'description' => 'Recognize and address biases in information sources.',
        ]);

        Practice::create([
            'skill_id' => $skill2->id,
            'description' => 'Analyze the source\'s background and potential motivations.',
            'order' => 1,
        ]);

        Practice::create([
            'skill_id' => $skill2->id,
            'description' => 'Compare the source\'s information with other reliable sources.',
            'order' => 2,
        ]);

        Practice::create([
            'skill_id' => $skill2->id,
            'description' => 'Reflect on how the bias might affect the team\'s decision-making.',
            'order' => 3,
        ]);

        // Skill 3: Creating and Communicating a New Timeline When Needed
        $skill3 = Skill::create([
            'name' => 'Creating and Communicating a New Timeline When Needed',
            'description' => 'Effectively manage and communicate timeline changes.',
        ]);

        Practice::create([
            'skill_id' => $skill3->id,
            'description' => 'Assess the impact of the change on the project timeline.',
            'order' => 1,
        ]);

        Practice::create([
            'skill_id' => $skill3->id,
            'description' => 'Draft a revised timeline and share it with the team.',
            'order' => 2,
        ]);

        Practice::create([
            'skill_id' => $skill3->id,
            'description' => 'Communicate the reasons for the change and ensure everyone is aligned.',
            'order' => 3,
        ]);
    }
}
