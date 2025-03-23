<?php

namespace Database\Factories;

use App\Models\Feedback;
use App\Models\Practice;
use App\Models\Skill;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Feedback>
 */
class FeedbackFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Feedback::class;
    
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $positive = $this->faker->boolean(70);
        
        return [
            'sender_id' => User::factory(),
            'recipient_id' => User::factory(),
            'team_id' => Team::factory(),
            'skill_id' => Skill::factory(),
            'practice_id' => Practice::factory(),
            'comments' => $this->faker->paragraph(),
            'is_positive' => $positive,
        ];
    }
    
    /**
     * Indicate that the feedback is positive.
     */
    public function positive(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_positive' => true,
            ];
        });
    }
    
    /**
     * Indicate that the feedback is negative.
     */
    public function negative(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_positive' => false,
            ];
        });
    }
} 