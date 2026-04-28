<?php

declare(strict_types=1);

namespace App\Models\Enums;

enum Ethnicity: string
{
    case ASIAN = 'ASIAN';
    case AFRICAN_AMERICAN = 'AFRICAN_AMERICAN';
    case HISPANIC_LATINO = 'HISPANIC LATINO';
    case CAUCASIAN = 'CAUCASIAN';
    case MIDDLE_EASTERN_OR_NORTH_AFRICAN = 'MIDDLE_EASTERN_OR_NORTH_AFRICAN';
    case PREFER_NOT_TO_SAY = 'PREFER_NOT_TO SAY';
}
