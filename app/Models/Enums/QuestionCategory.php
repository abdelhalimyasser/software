<?php

declare(strict_types=1);

namespace App\Models\Enums;

enum QuestionCategory: string
{
    case PROBLEM_SOLVING = 'PROBLEM_SOLVING';
    case DATA_ENGINEERING = 'DATA_ENGINEERING';
    case DATA_SCIENCE = 'DATA_SCIENCE';
    case DATA_ANALYSIS = 'DATA_ANALYSIS';
    case MACHINE_LEARNING = 'MACHINE_LEARNING';
    case DEEP_LEARNING = 'DEEP_LEARNING';
    case BACKEND = 'BACKEND';
}
