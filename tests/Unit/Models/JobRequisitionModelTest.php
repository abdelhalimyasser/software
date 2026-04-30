<?php

namespace Tests\Unit\Models;

use App\Models\Enums\JobStatus;
use App\Models\Enums\UserRole;
use App\Models\JobRequisition;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class JobRequisitionModelTest extends TestCase
{
    use DatabaseMigrations;

    public function test_fillable_contains_expected_attributes(): void
    {
        $model = new JobRequisition();
        $expected = ['title', 'description', 'department', 'created_by', 'status', 'status_updated_by'];

        $this->assertEquals($expected, $model->getFillable());
    }

    public function test_status_is_cast_to_job_status_enum(): void
    {
        $user = $this->makeUser();
        $job = JobRequisition::create([
            'title' => 'PHP Developer',
            'description' => 'Senior PHP developer needed',
            'department' => 'Engineering',
            'created_by' => $user->id,
            'status' => JobStatus::PENDING,
        ]);

        $fresh = JobRequisition::find($job->id);
        $this->assertInstanceOf(JobStatus::class, $fresh->status);
        $this->assertSame(JobStatus::PENDING, $fresh->status);
    }

    public function test_creator_relationship_returns_user(): void
    {
        $user = $this->makeUser();
        $job = JobRequisition::create([
            'title' => 'Designer',
            'description' => 'UI designer',
            'department' => 'Design',
            'created_by' => $user->id,
            'status' => JobStatus::PENDING,
        ]);

        $this->assertNotNull($job->creator);
        $this->assertSame($user->id, $job->creator->id);
    }

    public function test_status_updater_relationship_returns_user(): void
    {
        $creator = $this->makeUser(['email' => 'creator@test.com']);
        $manager = $this->makeUser(['email' => 'manager@test.com', 'phone_number' => '0100000001', 'role' => UserRole::DEPARTMENT_MANAGER->value]);

        $job = JobRequisition::create([
            'title' => 'QA Engineer',
            'description' => 'Testing specialist',
            'department' => 'QA',
            'created_by' => $creator->id,
            'status' => JobStatus::APPROVED,
            'status_updated_by' => $manager->id,
        ]);

        $this->assertNotNull($job->statusUpdater);
        $this->assertSame($manager->id, $job->statusUpdater->id);
    }

    public function test_status_updater_is_null_when_not_set(): void
    {
        $user = $this->makeUser();
        $job = JobRequisition::create([
            'title' => 'Pending Job',
            'description' => 'Not yet reviewed',
            'department' => 'Engineering',
            'created_by' => $user->id,
            'status' => JobStatus::PENDING,
        ]);

        $this->assertNull($job->statusUpdater);
    }

    public function test_can_create_job_with_all_fields(): void
    {
        $user = $this->makeUser();
        $job = JobRequisition::create([
            'title' => 'Full Stack Dev',
            'description' => 'React + Laravel',
            'department' => 'Product',
            'created_by' => $user->id,
            'status' => JobStatus::PENDING,
        ]);

        $this->assertDatabaseHas('job_requisitions', [
            'title' => 'Full Stack Dev',
            'department' => 'Product',
            'status' => 'PENDING',
            'created_by' => $user->id,
        ]);
    }

    private function makeUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'phone_number' => '0100000000',
            'password' => 'password',
            'role' => UserRole::EMPLOYEE->value,
        ], $overrides));
    }
}
