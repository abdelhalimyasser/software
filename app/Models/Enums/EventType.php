<?php

declare(strict_types=1);

namespace App\Models\Enums;

enum EventType: string
{
    case FOUCUS_LOSS = 'FOUCUS_LOSS';
    case TAB_SWITCH = 'TAB_SWITCH';
    case HEARTBEAT = 'HEARTBEAT';
    case ERROR_SUBMISSION = 'ERROR_SUBMISSION';
}
