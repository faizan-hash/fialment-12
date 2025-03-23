<?php

namespace Database\Seeders;

use App\Models\SkillArea;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SkillAreasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
            [
                'name' => 'Collaboration',
                'description' => 'Skills related to working effectively with others',
                'color' => '#CC0000',
            ],
            [
                'name' => 'Communication',
                'description' => 'Skills related to expressing ideas and information',
                'color' => '#FF9900',
            ],
            [
                'name' => 'Critical Thinking',
                'description' => 'Skills related to analyzing and evaluating information',
                'color' => '#33CC33',
            ],
            [
                'name' => 'Self-Awareness & Self-Management',
                'description' => 'Skills related to understanding and regulating oneself',
                'color' => '#0066CC',
            ],
            [
                'name' => 'Project Management',
                'description' => 'Skills related to planning and executing projects',
                'color' => '#660099',
            ],
        ];

        foreach ($areas as $area) {
            SkillArea::create($area);
        }
    }
} 