<?php

namespace Database\Factories;

use App\Models\Assessment;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssessmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence,
            'duration_minutes' => 60,
            'pass_mark' => 50,
            'total_mark' => 100,
            'cooldown_period' => 24,
            'distribution_rules' => ['HARD' => 2, 'MEDIUM' => 3, 'BASIC' => 5],
            'stage' => 'TECHNICAL',
        ];
    }
}

