<?php

namespace App\Models\Enums;

enum ApplicationStatus: string
{
    case PENDING = 'PENDING';
    case APPLIED = 'APPLIED';
    case IN_REVIEW = 'IN_REVIEW';
    case ACCEPTED = 'ACCEPTED';
    case REJECTED = 'REJECTED';
}
