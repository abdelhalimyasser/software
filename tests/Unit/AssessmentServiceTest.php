<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Application;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Question;
use App\Services\AssessmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AssessmentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AssessmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AssessmentService();
    }

    public function test_starts_attempt_successfully_with_distribution()
    {
        $application = Application::factory()->create();
        $assessment = Assessment::factory()->create([
            'cooldown_period' => 24,
            'distribution_rules' => ['HARD' => 1, 'MEDIUM' => 1]
        ]);

        Question::factory()->create(['difficulty_level' => 'HARD']);
        Question::factory()->create(['difficulty_level' => 'MEDIUM']);

        $attempt = $this->service->startAttempt($application, $assessment);

        $this->assertNotNull($attempt);
        $this->assertEquals($application->id, $attempt->application_id);
        $this->assertEquals('IN_PROGRESS', $attempt->status);
        $this->assertCount(2, $attempt->questions);
    }

    public function test_fails_due_to_cooldown_period()
    {
        $application = Application::factory()->create();
        $assessment = Assessment::factory()->create([
            'cooldown_period' => 24,
            'distribution_rules' => ['HARD' => 1]
        ]);

        AssessmentAttempt::factory()->create([
            'application_id' => $application->id,
            'assessment_id' => $assessment->id,
            'status' => 'FAILED',
            'completed_at' => now()->subHours(10) // 10 < 24 hours cooldown
        ]);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Cooldown period active');
        $this->expectExceptionCode(403);

        $this->service->startAttempt($application, $assessment);
    }

    public function test_retake_adjusts_distribution_shifting_hard_to_medium()
    {
        $application = Application::factory()->create();
        $assessment = Assessment::factory()->create([
            'cooldown_period' => 24,
            'distribution_rules' => ['HARD' => 2, 'MEDIUM' => 1]
        ]);

        Question::factory()->create(['difficulty_level' => 'HARD']);
        Question::factory()->count(2)->create(['difficulty_level' => 'MEDIUM']);

        // Satisfies cooldown, allowing retake
        AssessmentAttempt::factory()->create([
            'application_id' => $application->id,
            'assessment_id' => $assessment->id,
            'status' => 'FAILED',
            'score' => 45, // Scored < pass mark (50) but >= 40, so shift HARD -> MEDIUM
            'completed_at' => now()->subHours(30)
        ]);

        $attempt = $this->service->startAttempt($application, $assessment);

        // Rules started as: HARD => 2, MEDIUM => 1
        // Retake logic logic should shift it to HARD => 1, MEDIUM => 2
        $questions = $attempt->questions;
        
        $hardCount = $questions->where('difficulty_level', 'HARD')->count();
        $mediumCount = $questions->where('difficulty_level', 'MEDIUM')->count();

        $this->assertEquals(1, $hardCount);
        $this->assertEquals(2, $mediumCount);
    }
}
