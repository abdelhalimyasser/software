<?php

namespace Database\Factories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'question' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'category' => 'PROGRAMMING',
            'difficulty_level' => 'MEDIUM',
        ];
    }
}
