<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\User;
use App\Models\JobPost;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'candidate_id' => User::factory(),
            'job_id' => JobPost::factory(),
            'status' => 'PENDING',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

