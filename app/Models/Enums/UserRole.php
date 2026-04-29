<?php

declare(strict_types=1);

namespace App\Models\Enums;

enum UserRole: string
{
    case CANDIDATE = 'CANDIDATE';
    case EMPLOYEE = 'EMPLOYEE';
    case HR_ADMIN = 'HR_ADMIN';
    case INTERVIEWER = 'INTERVIEWER';
    case SHADOW_INTERVIEWER = 'SHADOW_INTERVIEWER';
    case DEPARTMENT_MANAGER = 'DEPARTMENT_MANAGER';
}
