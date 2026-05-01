<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Enums\JobStatus;
use App\Models\Enums\UserRole;
use App\Models\JobPost;
use App\Models\User;
use App\Notifications\JobRequiresApprovalNotification;
use App\Notifications\JobStatusUpdatedNotification;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class JobPostControllerTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\Authenticate::class);
    }

    public function test_index_returns_all_jobs_for_hr_admin()
    {
        $hrAdmin = $this->makeUserAs(UserRole::HR_ADMIN);
        
        JobPost::create(['title' => 'Job 1', 'description' => 'Desc', 'department' => 'IT', 'status' => JobStatus::PENDING, 'created_by' => $hrAdmin->id]);
        JobPost::create(['title' => 'Job 2', 'description' => 'Desc', 'department' => 'IT', 'status' => JobStatus::PENDING, 'created_by' => $hrAdmin->id]);
        JobPost::create(['title' => 'Job 3', 'description' => 'Desc', 'department' => 'IT', 'status' => JobStatus::APPROVED, 'created_by' => $hrAdmin->id]);

        $response = $this->actingAs($hrAdmin)->getJson('/api/v1/jobs');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('jobs.data'));
    }

    public function test_index_returns_matched_jobs_for_candidate()
    {
        $candidate = $this->makeUserAs(UserRole::CANDIDATE, [
            'experience_years' => 5,
            'skills' => ['PHP', 'Laravel', 'MySQL']
        ]);
        $hrAdmin = $this->makeUserAs(UserRole::HR_ADMIN);

        // Perfect match
        JobPost::create([
            'title' => 'Job 1', 'description' => 'Desc', 'department' => 'IT',
            'status' => JobStatus::APPROVED,
            'experience_level' => 3,
            'skills' => ['PHP', 'Laravel'],
            'created_by' => $hrAdmin->id
        ]);

        // Match with no required skills
        JobPost::create([
            'title' => 'Job 2', 'description' => 'Desc', 'department' => 'IT',
            'status' => JobStatus::APPROVED,
            'experience_level' => 2,
            'skills' => [],
            'created_by' => $hrAdmin->id
        ]);

        // Mismatch: Not enough experience
        JobPost::create([
            'title' => 'Job 3', 'description' => 'Desc', 'department' => 'IT',
            'status' => JobStatus::APPROVED,
            'experience_level' => 8,
            'skills' => ['PHP'],
            'created_by' => $hrAdmin->id
        ]);

        // Mismatch: Missing skills
        JobPost::create([
            'title' => 'Job 4', 'description' => 'Desc', 'department' => 'IT',
            'status' => JobStatus::APPROVED,
            'experience_level' => 3,
            'skills' => ['Python', 'Django'],
            'created_by' => $hrAdmin->id
        ]);

        $response = $this->actingAs($candidate)->getJson('/api/v1/jobs');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('jobs'));
    }

    public function test_store_creates_job_for_hr_admin_and_notifies_managers()
    {
        $hrAdmin = $this->makeUserAs(UserRole::HR_ADMIN);
        $manager = $this->makeUserAs(UserRole::DEPARTMENT_MANAGER);

        $payload = [
            'title' => 'Software Engineer',
            'description' => 'Great job',
            'department' => 'Engineering',
            'experience_level' => 3,
        ];

        $response = $this->actingAs($hrAdmin)->postJson('/api/v1/jobs', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('job_posts', [
            'title' => 'Software Engineer',
            'status' => JobStatus::PENDING->value
        ]);

        Notification::assertSentTo($manager, JobRequiresApprovalNotification::class);
    }

    public function test_approve_updates_status_and_notifies_hr()
    {
        $manager = $this->makeUserAs(UserRole::DEPARTMENT_MANAGER);
        $hrAdmin = $this->makeUserAs(UserRole::HR_ADMIN);
        $job = JobPost::create(['title' => 'Job 1', 'description' => 'Desc', 'department' => 'IT', 'status' => JobStatus::PENDING, 'created_by' => $hrAdmin->id]);

        $payload = ['reason' => 'Approved budget'];

        $response = $this->actingAs($manager)->postJson("/api/v1/jobs/{$job->id}/approve", $payload);

        $response->assertStatus(200);
        $this->assertEquals(JobStatus::APPROVED, $job->fresh()->status);
        $this->assertEquals('Approved budget', $job->fresh()->status_reason);

        Notification::assertSentTo($job->creator, JobStatusUpdatedNotification::class);
    }

    public function test_reject_updates_status_and_notifies_hr()
    {
        $manager = $this->makeUserAs(UserRole::DEPARTMENT_MANAGER);
        $hrAdmin = $this->makeUserAs(UserRole::HR_ADMIN);
        $job = JobPost::create(['title' => 'Job 1', 'description' => 'Desc', 'department' => 'IT', 'status' => JobStatus::PENDING, 'created_by' => $hrAdmin->id]);

        $payload = ['reason' => 'No budget'];

        $response = $this->actingAs($manager)->postJson("/api/v1/jobs/{$job->id}/reject", $payload);

        $response->assertStatus(200);
        $this->assertEquals(JobStatus::REJECTED, $job->fresh()->status);
        $this->assertEquals('No budget', $job->fresh()->status_reason);

        Notification::assertSentTo($job->creator, JobStatusUpdatedNotification::class);
    }

    private function makeUserAs(UserRole $role, array $overrides = []): User
    {
        $data = array_merge([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => fake()->unique()->numerify('01#########'),
            'password' => 'password',
            'role' => $role->value,
        ], $overrides);

        $user = User::create($data);
        return User::find($user->id);
    }
}
