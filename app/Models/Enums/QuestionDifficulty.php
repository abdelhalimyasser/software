<?php

declare(strict_types=1);

namespace App\Models\Enums;

enum QuestionDifficulty: string
{
    case HARD = 'HARD';
    case MEDIUM = 'MEDIUM';
    case BASIC = 'BASIC';
}
