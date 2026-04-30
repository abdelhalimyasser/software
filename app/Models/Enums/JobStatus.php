<?php

declare(strict_types=1);

namespace App\Models\Enums;

enum JobStatus: string
{
    case PENDING = 'PENDING';
    case REJECTED = 'REJECTED';
    case APPROVED = 'APPROVED';
    case CLOSED = 'CLOSED';
}
