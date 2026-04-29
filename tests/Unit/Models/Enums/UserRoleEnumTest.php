<?php

namespace Tests\Unit\Models\Enums;

use App\Models\Enums\UserRole;
use PHPUnit\Framework\TestCase;

class UserRoleEnumTest extends TestCase
{
    public function test_all_roles_have_correct_values(): void
    {
        $this->assertSame('CANDIDATE', UserRole::CANDIDATE->value);
        $this->assertSame('EMPLOYEE', UserRole::EMPLOYEE->value);
        $this->assertSame('HR_ADMIN', UserRole::HR_ADMIN->value);
        $this->assertSame('INTERVIEWER', UserRole::INTERVIEWER->value);
        $this->assertSame('SHADOW_INTERVIEWER', UserRole::SHADOW_INTERVIEWER->value);
        $this->assertSame('DEPARTMENT_MANAGER', UserRole::DEPARTMENT_MANAGER->value);
    }

    public function test_role_count_is_six(): void
    {
        $this->assertCount(6, UserRole::cases());
    }

    public function test_role_can_be_created_from_value(): void
    {
        $role = UserRole::from('CANDIDATE');
        $this->assertSame(UserRole::CANDIDATE, $role);
    }

    public function test_invalid_role_throws_value_error(): void
    {
        $this->expectException(\ValueError::class);
        UserRole::from('INVALID_ROLE');
    }
}
