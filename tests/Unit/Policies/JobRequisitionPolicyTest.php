<?php

namespace Tests\Unit\Policies;

use App\Models\Candidate;
use App\Models\DepartmentManager;
use App\Models\Employee;
use App\Models\Enums\JobStatus;
use App\Models\Enums\UserRole;
use App\Models\JobRequisition;
use App\Models\User;
use App\Policies\JobRequisitionPolicy;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class JobRequisitionPolicyTest extends TestCase
{
    use DatabaseMigrations;

    private JobRequisitionPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new JobRequisitionPolicy();
    }

    // ── viewAny ─────────────────────────────

    public function test_view_any_allows_all_users(): void
    {
        $candidate = $this->makeUserAs(UserRole::CANDIDATE, 'cand@test.com');
        $employee = $this->makeUserAs(UserRole::EMPLOYEE, 'emp@test.com');
        $manager = $this->makeUserAs(UserRole::DEPARTMENT_MANAGER, 'mgr@test.com');

        $this->assertTrue($this->policy->viewAny($candidate));
        $this->assertTrue($this->policy->viewAny($employee));
        $this->assertTrue($this->policy->viewAny($manager));
    }

    // ── view ────────────────────────────────

    public function test_view_allows_employees_to_see_any_job(): void
    {
        $employee = $this->makeUserAs(UserRole::EMPLOYEE);
        $job = $this->makeJob(JobStatus::PENDING);

        $this->assertTrue($this->policy->view($employee, $job));
    }

    public function test_view_allows_candidates_to_see_approved_jobs(): void
    {
        $candidate = $this->makeUserAs(UserRole::CANDIDATE);
        $job = $this->makeJob(JobStatus::APPROVED);

        $this->assertTrue($this->policy->view($candidate, $job));
    }

    public function test_view_blocks_candidates_from_pending_jobs(): void
    {
        $candidate = $this->makeUserAs(UserRole::CANDIDATE);
        $job = $this->makeJob(JobStatus::PENDING);

        $this->assertFalse($this->policy->view($candidate, $job));
    }

    public function test_view_blocks_candidates_from_rejected_jobs(): void
    {
        $candidate = $this->makeUserAs(UserRole::CANDIDATE);
        $job = $this->makeJob(JobStatus::REJECTED);

        $this->assertFalse($this->policy->view($candidate, $job));
    }

    // ── create ──────────────────────────────

    public function test_create_allows_hr_admin(): void
    {
        $hrAdmin = $this->makeUserAs(UserRole::HR_ADMIN);

        $this->assertTrue($this->policy->create($hrAdmin));
    }

    public function test_create_blocks_candidate(): void
    {
        $candidate = $this->makeUserAs(UserRole::CANDIDATE, 'cand2@test.com');

        $this->assertFalse($this->policy->create($candidate));
    }

    public function test_create_blocks_regular_employee(): void
    {
        $employee = $this->makeUserAs(UserRole::EMPLOYEE, 'emp2@test.com');

        $this->assertFalse($this->policy->create($employee));
    }

    public function test_create_blocks_department_manager(): void
    {
        $manager = $this->makeUserAs(UserRole::DEPARTMENT_MANAGER, 'mgr@test.com');

        $this->assertFalse($this->policy->create($manager));
    }

    // ── approve ─────────────────────────────

    public function test_approve_allows_department_manager(): void
    {
        $manager = $this->makeUserAs(UserRole::DEPARTMENT_MANAGER, 'mgr@test.com');
        $job = $this->makeJob(JobStatus::PENDING);

        $this->assertTrue($this->policy->approve($manager, $job));
    }

    public function test_approve_blocks_hr_admin(): void
    {
        $hrAdmin = $this->makeUserAs(UserRole::HR_ADMIN, 'hr@test.com');
        $job = $this->makeJob(JobStatus::PENDING);

        $this->assertFalse($this->policy->approve($hrAdmin, $job));
    }

    public function test_approve_blocks_candidate(): void
    {
        $candidate = $this->makeUserAs(UserRole::CANDIDATE);
        $job = $this->makeJob(JobStatus::PENDING);

        $this->assertFalse($this->policy->approve($candidate, $job));
    }

    // ── reject ──────────────────────────────

    public function test_reject_allows_department_manager(): void
    {
        $manager = $this->makeUserAs(UserRole::DEPARTMENT_MANAGER, 'mgr@test.com');
        $job = $this->makeJob(JobStatus::PENDING);

        $this->assertTrue($this->policy->reject($manager, $job));
    }

    public function test_reject_blocks_regular_employee(): void
    {
        $employee = $this->makeUserAs(UserRole::EMPLOYEE);
        $job = $this->makeJob(JobStatus::PENDING);

        $this->assertFalse($this->policy->reject($employee, $job));
    }

    // ── Helpers ─────────────────────────────

    /**
     * Create a user and re-fetch so Parental resolves the correct child class.
     */
    private function makeUserAs(UserRole $role, string $email = 'user@test.com'): User
    {
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $email,
            'phone_number' => fake()->unique()->numerify('01#########'),
            'password' => 'password',
            'role' => $role->value,
        ]);

        // Re-fetch so Parental resolves to the correct child model (Candidate, Employee, HrAdmin, etc.)
        return User::find($user->id);
    }

    private function makeJob(JobStatus $status): JobRequisition
    {
        $creator = User::firstOrCreate(
            ['email' => 'creator@test.com'],
            [
                'first_name' => 'Creator',
                'last_name' => 'User',
                'phone_number' => '0100099999',
                'password' => 'password',
                'role' => UserRole::HR_ADMIN->value,
            ]
        );

        return JobRequisition::create([
            'title' => 'Test Job',
            'description' => 'A test position',
            'department' => 'Engineering',
            'created_by' => $creator->id,
            'status' => $status,
        ]);
    }
}
