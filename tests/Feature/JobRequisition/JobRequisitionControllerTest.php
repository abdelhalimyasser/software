<?php

namespace Tests\Feature\JobRequisition;

use App\Models\Enums\JobStatus;
use App\Models\Enums\UserRole;
use App\Models\JobRequisition;
use App\Models\User;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class JobRequisitionControllerTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Bypass auth:sanctum middleware (not installed) for all tests.
     * actingAs() still sets the authenticated user for Gate checks.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(Authenticate::class);
    }

    // ══════════════════════════════════════════
    //  GET /v1/jobs — index
    // ══════════════════════════════════════════

    public function test_index_returns_all_jobs_for_employee(): void
    {
        $employee = $this->actAs(UserRole::EMPLOYEE);
        $this->createJob(status: JobStatus::PENDING, creatorId: $employee->id);
        $this->createJob(status: JobStatus::APPROVED, creatorId: $employee->id);

        $response = $this->actingAs($employee)->getJson('/api/v1/jobs');

        $response->assertOk();
        $response->assertJsonPath('message', 'Jobs retrieved successfully.');
        $response->assertJsonCount(2, 'jobs.data');
    }

    public function test_index_returns_only_approved_jobs_for_candidate(): void
    {
        $creator = $this->actAs(UserRole::EMPLOYEE, 'creator@test.com');
        $this->createJob(status: JobStatus::PENDING, creatorId: $creator->id);
        $this->createJob(status: JobStatus::APPROVED, creatorId: $creator->id);
        $this->createJob(status: JobStatus::REJECTED, creatorId: $creator->id);

        $candidate = $this->actAs(UserRole::CANDIDATE, 'cand@test.com');

        $response = $this->actingAs($candidate)->getJson('/api/v1/jobs');

        $response->assertOk();
        $response->assertJsonCount(1, 'jobs.data');
    }

    public function test_index_returns_paginated_results(): void
    {
        $employee = $this->actAs(UserRole::EMPLOYEE);
        for ($i = 0; $i < 15; $i++) {
            $this->createJob(
                status: JobStatus::PENDING,
                creatorId: $employee->id,
                title: "Job $i"
            );
        }

        $response = $this->actingAs($employee)->getJson('/api/v1/jobs');

        $response->assertOk();
        $response->assertJsonCount(10, 'jobs.data');
        $response->assertJsonPath('jobs.last_page', 2);
    }

    public function test_index_includes_creator_relationship(): void
    {
        $employee = $this->actAs(UserRole::EMPLOYEE);
        $this->createJob(status: JobStatus::PENDING, creatorId: $employee->id);

        $response = $this->actingAs($employee)->getJson('/api/v1/jobs');

        $response->assertOk();
        $response->assertJsonStructure(['jobs' => ['data' => [['creator' => ['id', 'first_name']]]]]);
    }

    // ══════════════════════════════════════════
    //  GET /v1/jobs/{job} — show
    // ══════════════════════════════════════════

    public function test_show_returns_job_for_employee(): void
    {
        $employee = $this->actAs(UserRole::EMPLOYEE);
        $job = $this->createJob(status: JobStatus::PENDING, creatorId: $employee->id);

        $response = $this->actingAs($employee)->getJson("/api/v1/jobs/{$job->id}");

        $response->assertOk();
        $response->assertJsonPath('job.id', $job->id);
        $response->assertJsonPath('job.title', $job->title);
        $response->assertJsonStructure(['job' => ['id', 'title', 'description', 'department', 'status', 'creator']]);
    }

    public function test_show_returns_approved_job_for_candidate(): void
    {
        $creator = $this->actAs(UserRole::EMPLOYEE, 'creator@test.com');
        $job = $this->createJob(status: JobStatus::APPROVED, creatorId: $creator->id);
        $candidate = $this->actAs(UserRole::CANDIDATE, 'cand@test.com');

        $response = $this->actingAs($candidate)->getJson("/api/v1/jobs/{$job->id}");

        $response->assertOk();
    }

    public function test_show_blocks_candidate_from_pending_job(): void
    {
        $creator = $this->actAs(UserRole::EMPLOYEE, 'creator@test.com');
        $job = $this->createJob(status: JobStatus::PENDING, creatorId: $creator->id);
        $candidate = $this->actAs(UserRole::CANDIDATE, 'cand@test.com');

        $response = $this->actingAs($candidate)->getJson("/api/v1/jobs/{$job->id}");

        $response->assertForbidden();
    }

    public function test_show_returns_404_for_nonexistent_job(): void
    {
        $employee = $this->actAs(UserRole::EMPLOYEE);

        $response = $this->actingAs($employee)->getJson('/api/v1/jobs/99999');

        $response->assertNotFound();
    }

    // ══════════════════════════════════════════
    //  POST /v1/jobs/add — store
    // ══════════════════════════════════════════

    public function test_store_creates_job_for_hr_admin(): void
    {
        $hrAdmin = $this->actAs(UserRole::HR_ADMIN);

        $response = $this->actingAs($hrAdmin)->postJson('/api/v1/jobs/add', [
            'title' => 'Senior PHP Developer',
            'description' => 'We need an experienced Laravel developer.',
            'department' => 'Engineering',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('message', 'Job requisition created and pending approval.');
        $response->assertJsonPath('job.title', 'Senior PHP Developer');
        $response->assertJsonPath('job.status', JobStatus::PENDING->value);
        $response->assertJsonPath('job.created_by', $hrAdmin->id);
        $this->assertDatabaseHas('job_requisitions', [
            'title' => 'Senior PHP Developer',
            'department' => 'Engineering',
            'status' => 'PENDING',
        ]);
    }

    public function test_store_blocks_regular_employee(): void
    {
        $employee = $this->actAs(UserRole::EMPLOYEE);

        $response = $this->actingAs($employee)->postJson('/api/v1/jobs/add', [
            'title' => 'Should Fail',
            'description' => 'Not allowed.',
            'department' => 'Engineering',
        ]);

        $response->assertForbidden();
    }

    public function test_store_blocks_candidate(): void
    {
        $candidate = $this->actAs(UserRole::CANDIDATE);

        $response = $this->actingAs($candidate)->postJson('/api/v1/jobs/add', [
            'title' => 'Should Fail',
            'description' => 'Not allowed.',
            'department' => 'Engineering',
        ]);

        $response->assertForbidden();
    }

    public function test_store_blocks_department_manager(): void
    {
        $manager = $this->actAs(UserRole::DEPARTMENT_MANAGER, 'mgr@test.com');

        $response = $this->actingAs($manager)->postJson('/api/v1/jobs/add', [
            'title' => 'Should Fail',
            'description' => 'Not allowed.',
            'department' => 'Engineering',
        ]);

        $response->assertForbidden();
    }

    public function test_store_validates_required_fields(): void
    {
        $hrAdmin = $this->actAs(UserRole::HR_ADMIN);

        $response = $this->actingAs($hrAdmin)->postJson('/api/v1/jobs/add', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'description', 'department']);
    }

    public function test_store_validates_title_max_length(): void
    {
        $hrAdmin = $this->actAs(UserRole::HR_ADMIN);

        $response = $this->actingAs($hrAdmin)->postJson('/api/v1/jobs/add', [
            'title' => str_repeat('A', 256),
            'description' => 'Valid description',
            'department' => 'Engineering',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);
    }

    // ══════════════════════════════════════════
    //  POST /v1/jobs/{job}/approve — approve
    // ══════════════════════════════════════════

    public function test_approve_updates_status_for_department_manager(): void
    {
        $creator = $this->actAs(UserRole::HR_ADMIN, 'hr@test.com');
        $job = $this->createJob(status: JobStatus::PENDING, creatorId: $creator->id);
        $manager = $this->actAs(UserRole::DEPARTMENT_MANAGER, 'mgr@test.com');

        $response = $this->actingAs($manager)->postJson("/api/v1/jobs/{$job->id}/approve");

        $response->assertOk();
        $response->assertJsonPath('message', 'Job requisition approved successfully.');
        $response->assertJsonPath('job.status', JobStatus::APPROVED->value);
        $this->assertDatabaseHas('job_requisitions', [
            'id' => $job->id,
            'status' => 'APPROVED',
            'status_updated_by' => $manager->id,
        ]);
    }

    public function test_approve_blocks_hr_admin(): void
    {
        $hrAdmin = $this->actAs(UserRole::HR_ADMIN);
        $job = $this->createJob(status: JobStatus::PENDING, creatorId: $hrAdmin->id);

        $response = $this->actingAs($hrAdmin)->postJson("/api/v1/jobs/{$job->id}/approve");

        $response->assertForbidden();
    }

    public function test_approve_blocks_candidate(): void
    {
        $creator = $this->actAs(UserRole::HR_ADMIN, 'hr@test.com');
        $job = $this->createJob(status: JobStatus::PENDING, creatorId: $creator->id);
        $candidate = $this->actAs(UserRole::CANDIDATE, 'cand@test.com');

        $response = $this->actingAs($candidate)->postJson("/api/v1/jobs/{$job->id}/approve");

        $response->assertForbidden();
    }

    // ══════════════════════════════════════════
    //  POST /v1/jobs/{job}/reject — reject
    // ══════════════════════════════════════════

    public function test_reject_updates_status_for_department_manager(): void
    {
        $creator = $this->actAs(UserRole::HR_ADMIN, 'hr@test.com');
        $job = $this->createJob(status: JobStatus::PENDING, creatorId: $creator->id);
        $manager = $this->actAs(UserRole::DEPARTMENT_MANAGER, 'mgr@test.com');

        $response = $this->actingAs($manager)->postJson("/api/v1/jobs/{$job->id}/reject");

        $response->assertOk();
        $response->assertJsonPath('message', 'Job requisition rejected.');
        $response->assertJsonPath('job.status', JobStatus::REJECTED->value);
        $this->assertDatabaseHas('job_requisitions', [
            'id' => $job->id,
            'status' => 'REJECTED',
            'status_updated_by' => $manager->id,
        ]);
    }

    public function test_reject_blocks_regular_employee(): void
    {
        $creator = $this->actAs(UserRole::HR_ADMIN, 'hr@test.com');
        $job = $this->createJob(status: JobStatus::PENDING, creatorId: $creator->id);
        $employee = $this->actAs(UserRole::EMPLOYEE, 'emp@test.com');

        $response = $this->actingAs($employee)->postJson("/api/v1/jobs/{$job->id}/reject");

        $response->assertForbidden();
    }

    public function test_reject_returns_404_for_nonexistent_job(): void
    {
        $manager = $this->actAs(UserRole::DEPARTMENT_MANAGER, 'mgr@test.com');

        $response = $this->actingAs($manager)->postJson('/api/v1/jobs/99999/reject');

        $response->assertNotFound();
    }

    // ══════════════════════════════════════════
    //  Helpers
    // ══════════════════════════════════════════

    private function actAs(UserRole $role, string $email = 'user@test.com'): User
    {
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => $role->value,
            'email' => $email,
            'phone_number' => fake()->unique()->numerify('01#########'),
            'password' => 'password',
            'role' => $role->value,
        ]);

        // Re-fetch so Parental resolves to the correct child model
        return User::find($user->id);
    }

    private function createJob(JobStatus $status, int $creatorId, string $title = 'Test Job'): JobRequisition
    {
        return JobRequisition::create([
            'title' => $title,
            'description' => 'A test job description',
            'department' => 'Engineering',
            'created_by' => $creatorId,
            'status' => $status,
        ]);
    }
}
