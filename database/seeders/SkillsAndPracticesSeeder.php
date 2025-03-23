<?php

namespace Database\Seeders;

use App\Models\Practice;
use App\Models\Skill;
use App\Models\SkillArea;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SkillsAndPracticesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the Collaboration skill area
        $collaborationArea = SkillArea::where('name', 'Collaboration')->first();
        
        // Skill 1: Offering a Teammate Assistance
        $skill1 = Skill::create([
            'name' => 'Offering a Teammate Assistance',
            'description' => 'Identify opportunities to help teammates and provide effective assistance.',
            'skill_area_id' => $collaborationArea->id,
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

        // Get the Critical Thinking skill area
        $criticalThinkingArea = SkillArea::where('name', 'Critical Thinking')->first();
        
        // Skill 2: Identifying Bias(es) from a Particular Source
        $skill2 = Skill::create([
            'name' => 'Identifying Bias(es) from a Particular Source',
            'description' => 'Recognize and address biases in information sources.',
            'skill_area_id' => $criticalThinkingArea->id,
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

        // Get the Project Management skill area
        $projectManagementArea = SkillArea::where('name', 'Project Management')->first();
        
        // Skill 3: Creating and Communicating a New Timeline When Needed
        $skill3 = Skill::create([
            'name' => 'Creating and Communicating a New Timeline When Needed',
            'description' => 'Effectively manage and communicate timeline changes.',
            'skill_area_id' => $projectManagementArea->id,
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
        
        // Add more skills to fill out the skill areas
        
        // Communication skill area
        $communicationArea = SkillArea::where('name', 'Communication')->first();
        
        $skill4 = Skill::create([
            'name' => 'Building Relationships',
            'description' => 'Establish and maintain positive professional relationships.',
            'skill_area_id' => $communicationArea->id,
        ]);
        
        Practice::create([
            'skill_id' => $skill4->id,
            'description' => 'Initiate conversations with new team members.',
            'order' => 1,
        ]);
        
        Practice::create([
            'skill_id' => $skill4->id,
            'description' => 'Show genuine interest in others\' perspectives and experiences.',
            'order' => 2,
        ]);
        
        Practice::create([
            'skill_id' => $skill4->id,
            'description' => 'Maintain regular communication with team members.',
            'order' => 3,
        ]);
        
        // Self-Awareness skill area
        $selfAwarenessArea = SkillArea::where('name', 'Self-Awareness & Self-Management')->first();
        
        $skill5 = Skill::create([
            'name' => 'Reflecting on Performance',
            'description' => 'Analyze personal performance and identify areas for improvement.',
            'skill_area_id' => $selfAwarenessArea->id,
        ]);
        
        Practice::create([
            'skill_id' => $skill5->id,
            'description' => 'Document personal successes and challenges after key activities.',
            'order' => 1,
        ]);
        
        Practice::create([
            'skill_id' => $skill5->id,
            'description' => 'Ask for specific feedback from peers and mentors.',
            'order' => 2,
        ]);
        
        Practice::create([
            'skill_id' => $skill5->id,
            'description' => 'Set concrete goals for improvement based on reflection.',
            'order' => 3,
        ]);
    }
}
