<?php

namespace App\Models;

use App\Models\Enums\UserRole;
use Parental\HasChildren;
use Parental\HasParent;

class Employee extends User
{
    use HasParent, HasChildren;

    protected array $childTypes = [
        UserRole::HR_ADMIN->value => UserRole::class,
        UserRole::INTERVIEWER->value => Interviewer::class,
        UserRole::DEPARTMENT_MANGER->value = DepartmentManager::class,
        UserRole::SHADOW_INTERVIEWER->value => ShadowInterviewer::class
    ];

    public function makeReferal(int $userId)
    {
        // Logic to create a referral for the given user ID
    }
}
