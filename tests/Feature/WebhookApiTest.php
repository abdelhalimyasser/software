<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AssessmentAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WebhookApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_process_moss_webhook()
    {
        $attempt = AssessmentAttempt::factory()->create();

        $payload = [
            'attempt_id' => $attempt->id,
            'plagiarism_score' => 84.5,
            'moss_report_url' => 'https://moss.stanford.edu/results/12345'
        ];

        $response = $this->postJson("/api/v1/webhooks/moss-results", $payload);

        $response->assertStatus(200)
                 ->assertJsonPath('data.plagiarism_score', 84.5);

        $this->assertDatabaseHas('assessment_attempts', [
            'id' => $attempt->id,
            'plagiarism_score' => 84.5,
            'moss_report_url' => 'https://moss.stanford.edu/results/12345'
        ]);
    }
}
