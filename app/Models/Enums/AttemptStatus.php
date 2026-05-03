<?php

namespace App\Models\Enums;

enum AttemptStatus: string
{
    case IN_PROGRESS = 'IN_PROGRESS';
    case PASSED = 'PASSED';
    case FAILED = 'FAILED';
}
