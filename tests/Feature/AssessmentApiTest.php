<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Application;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AssessmentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_start_assessment_attempt()
    {
        $application = Application::factory()->create();
        $assessment = Assessment::factory()->create();

        $response = $this->postJson("/api/v1/assessments/{$assessment->id}/start", [
            'application_id' => $application->id
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['message', 'data']);
                 
        $this->assertDatabaseHas('assessment_attempts', [
            'application_id' => $application->id,
            'assessment_id' => $assessment->id
        ]);
    }

    public function test_can_store_batched_logs()
    {
        $attempt = AssessmentAttempt::factory()->create();

        $payload = [
            'logs' => [
                [
                    'event_type' => 'TAB_SWITCH',
                    'occurred_at' => now()->format('Y-m-d H:i:s.v'),
                    'metadata' => ['tab' => 'Google']
                ],
                [
                    'event_type' => 'FOCUS_LOSS',
                    'occurred_at' => now()->format('Y-m-d H:i:s.v'),
                    'metadata' => null
                ]
            ]
        ];

        $response = $this->postJson("/api/v1/assessments/{$attempt->id}/logs", $payload);

        $response->assertStatus(201)
                 ->assertJson(['message' => '2 logs recorded successfully.']);

        $this->assertDatabaseHas('assessment_logs', [
            'assessment_attempt_id' => $attempt->id,
            'event_type' => 'TAB_SWITCH'
        ]);
    }
}
