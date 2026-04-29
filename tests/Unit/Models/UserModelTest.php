<?php

namespace Tests\Unit\Models;

use App\Models\Candidate;
use App\Models\DepartmentManager;
use App\Models\Employee;
use App\Models\Enums\UserRole;
use App\Models\HrAdmin;
use App\Models\Interviewer;
use App\Models\ShadowInterviewer;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use DatabaseMigrations;

    public function test_user_fillable_contains_expected_attributes(): void
    {
        $user = new User();
        $expected = [
            'name', 'first_name', 'last_name', 'birth_date',
            'email', 'phone_number', 'password', 'role', 'profile_picture_path',
        ];

        $this->assertEquals($expected, $user->getFillable());
    }

    public function test_user_casts_password_as_hashed(): void
    {
        $user = User::create([
            'first_name' => 'Hash',
            'last_name' => 'Test',
            'email' => 'hash@example.com',
            'phone_number' => '0100000000',
            'password' => 'plaintext',
            'role' => 'CANDIDATE',
        ]);

        $this->assertNotEquals('plaintext', $user->password);
        $this->assertTrue(Hash::check('plaintext', $user->password));
    }

    public function test_user_casts_skills_as_array(): void
    {
        $user = User::create([
            'first_name' => 'Skill',
            'last_name' => 'Test',
            'email' => 'skill@example.com',
            'phone_number' => '0100000001',
            'password' => 'password',
            'role' => 'CANDIDATE',
            'skills' => ['PHP', 'Laravel'],
        ]);

        // The 'skills' field uses JSON cast but is only in $fillable for Candidate child
        // Verify from DB
        $fresh = User::find($user->id);
        $this->assertIsArray($fresh->skills);
        $this->assertEquals(['PHP', 'Laravel'], $fresh->skills);
    }

    public function test_user_auto_computes_name_from_first_and_last(): void
    {
        $user = User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'auto@example.com',
            'phone_number' => '0100000002',
            'password' => 'password',
            'role' => 'CANDIDATE',
        ]);

        $this->assertSame('John Doe', $user->name);
    }

    public function test_user_preserves_explicit_name(): void
    {
        $user = User::create([
            'name' => 'Custom Name',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'custom@example.com',
            'phone_number' => '0100000003',
            'password' => 'password',
            'role' => 'CANDIDATE',
        ]);

        $this->assertSame('Custom Name', $user->name);
    }

    public function test_user_child_types_map_all_six_roles(): void
    {
        $user = new User();
        $childTypes = (new \ReflectionProperty($user, 'childTypes'))->getValue($user);

        $this->assertCount(6, $childTypes);
        $this->assertSame(Candidate::class, $childTypes[UserRole::CANDIDATE->value]);
        $this->assertSame(Employee::class, $childTypes[UserRole::EMPLOYEE->value]);
        $this->assertSame(HrAdmin::class, $childTypes[UserRole::HR_ADMIN->value]);
        $this->assertSame(Interviewer::class, $childTypes[UserRole::INTERVIEWER->value]);
        $this->assertSame(ShadowInterviewer::class, $childTypes[UserRole::SHADOW_INTERVIEWER->value]);
        $this->assertSame(DepartmentManager::class, $childTypes[UserRole::DEPARTMENT_MANAGER->value]);
    }

    public function test_user_resolves_to_candidate_when_role_is_candidate(): void
    {
        $user = User::create([
            'first_name' => 'Parental',
            'last_name' => 'Test',
            'email' => 'parental@example.com',
            'phone_number' => '0100000004',
            'password' => 'password',
            'role' => UserRole::CANDIDATE->value,
        ]);

        $fetched = User::find($user->id);
        $this->assertInstanceOf(Candidate::class, $fetched);
    }

    public function test_user_resolves_to_employee_when_role_is_employee(): void
    {
        $user = User::create([
            'first_name' => 'Emp',
            'last_name' => 'Test',
            'email' => 'emp@example.com',
            'phone_number' => '0100000005',
            'password' => 'password',
            'role' => UserRole::EMPLOYEE->value,
        ]);

        $fetched = User::find($user->id);
        $this->assertInstanceOf(Employee::class, $fetched);
    }
}
