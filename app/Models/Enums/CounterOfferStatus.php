<?php

declare(strict_types=1);

namespace App\Models\Enums;

enum CounterOfferStatus: string
{
    case PENDING_REVIEW = 'PENDING_REVIEW';
    case APPROVED = 'APPROVED';
    case DECLINED = 'DECLINED';
}
