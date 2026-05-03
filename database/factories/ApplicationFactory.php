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
            'job_post_id' => 1, // We could make a JobPost factory, but let's assume it exists or isn't strict constraint right now, else we'd use JobPost::factory()
            'status' => 'PENDING',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

