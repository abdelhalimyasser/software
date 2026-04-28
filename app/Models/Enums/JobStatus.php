<?php

declare(strict_types=1);

namespace App\Models\Enums;

enum JobStatus: string
{
    case DRAFT = 'DRAFT';
    case PENDING = 'PENDING';
    case APPROVEL = 'APPROVEL';
    case ACTIVE = 'ACTIVE';
    case CLOSED = 'CLOSED';
    case ARCHIVED = 'ARCHIVED';
}
