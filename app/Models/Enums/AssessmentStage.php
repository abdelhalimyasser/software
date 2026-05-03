<?php

namespace App\Models\Enums;

enum AssessmentStage: string
{
    case PRE_INTERVIEW = 'PRE_INTERVIEW';
    case DURING_INTERVIEW = 'DURING_INTERVIEW';
}