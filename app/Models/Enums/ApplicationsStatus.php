<?php

declare(strict_types=1);

namespace App\Models\Enums;

enum ApplicationsStatus: string
{
    case APPLIED = 'APPLIED';
    case TECHNICAL_TEST = 'TECHNICAL_TEST';
    case INTERVIEW = 'INTERVIEW';
    case OFFER = 'OFFER';
    case HIRED = 'HIRED';
    case REJECTED = 'REJECTED';
}
