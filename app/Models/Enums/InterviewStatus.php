<?php

declare(strict_types=1);

namespace App\Models\Enums;

enum InterviewStatus: string
{
    case SCHEDULED = 'SCHEDULED';
    case IN_PROGRESS = 'IN_PROGRESS';
    case COMPLETED = 'COMPLETED';
    case CANCELED = 'CANCELED';
}
