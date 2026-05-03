<?php

namespace Database\Factories;

use App\Models\JobPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobPostFactory extends Factory
{
    protected $model = JobPost::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->jobTitle,
            'description' => $this->faker->paragraph,
            'department' => 'Engineering',
            'location' => 'Remote',
            'skills' => ['PHP', 'Laravel'],
            'experience_level' => 3,
            'status' => 'PENDING',
            'created_by' => User::factory(),
        ];
    }
}
