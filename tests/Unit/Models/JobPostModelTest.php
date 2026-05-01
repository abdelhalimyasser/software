<?php

namespace Tests\Unit\Models;

use App\Models\Enums\JobStatus;
use App\Models\Enums\UserRole;
use App\Models\JobPost;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class JobPostModelTest extends TestCase
{
    use DatabaseMigrations;

    public function test_fillable_contains_expected_attributes(): void
    {
        $model = new JobPost();
        $expected = [
            'title', 'description', 'department', 'location', 'skills', 
            'experience_level', 'created_by', 'status', 'status_updated_by', 'status_reason'
        ];

        $this->assertEquals($expected, $model->getFillable());
    }

    public function test_status_is_cast_to_job_status_enum(): void
    {
        $user = $this->makeUser();
        $job = JobPost::create([
            'title' => 'PHP Developer',
            'description' => 'Senior PHP developer needed',
            'department' => 'Engineering',
            'created_by' => $user->id,
            'status' => JobStatus::PENDING,
        ]);

        $fresh = JobPost::find($job->id);
        $this->assertInstanceOf(JobStatus::class, $fresh->status);
        $this->assertSame(JobStatus::PENDING, $fresh->status);
    }

    public function test_skills_is_cast_to_array(): void
    {
        $user = $this->makeUser();
        $job = JobPost::create([
            'title' => 'PHP Developer',
            'description' => 'Senior PHP developer needed',
            'department' => 'Engineering',
            'created_by' => $user->id,
            'skills' => ['PHP', 'Laravel', 'MySQL'],
            'status' => JobStatus::PENDING,
        ]);

        $fresh = JobPost::find($job->id);
        $this->assertIsArray($fresh->skills);
        $this->assertCount(3, $fresh->skills);
        $this->assertEquals('Laravel', $fresh->skills[1]);
    }

    public function test_creator_relationship_returns_user(): void
    {
        $user = $this->makeUser();
        $job = JobPost::create([
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

        $job = JobPost::create([
            'title' => 'QA Engineer',
            'description' => 'Testing specialist',
            'department' => 'QA',
            'created_by' => $creator->id,
            'status' => JobStatus::APPROVED,
            'status_updated_by' => $manager->id,
            'status_reason' => 'Looks good',
        ]);

        $this->assertNotNull($job->statusUpdater);
        $this->assertSame($manager->id, $job->statusUpdater->id);
    }

    public function test_status_updater_is_null_when_not_set(): void
    {
        $user = $this->makeUser();
        $job = JobPost::create([
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
        $job = JobPost::create([
            'title' => 'Full Stack Dev',
            'description' => 'React + Laravel',
            'department' => 'Product',
            'location' => 'Remote',
            'skills' => ['React', 'Laravel'],
            'experience_level' => 3,
            'created_by' => $user->id,
            'status' => JobStatus::PENDING,
            'status_reason' => 'Initial creation',
        ]);

        $this->assertDatabaseHas('job_posts', [
            'title' => 'Full Stack Dev',
            'department' => 'Product',
            'location' => 'Remote',
            'experience_level' => 3,
            'status' => 'PENDING',
            'status_reason' => 'Initial creation',
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
