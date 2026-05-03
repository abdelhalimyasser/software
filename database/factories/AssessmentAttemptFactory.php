<?php

namespace Database\Factories;

use App\Models\AssessmentAttempt;
use App\Models\Application;
use App\Models\Assessment;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssessmentAttemptFactory extends Factory
{
    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'assessment_id' => Assessment::factory(),
            'status' => 'IN_PROGRESS',
            'started_at' => now(),
        ];
    }
}

