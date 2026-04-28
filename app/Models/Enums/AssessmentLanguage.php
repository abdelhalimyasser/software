<?php

declare(strict_types=1);

namespace App\Models\Enums;

enum AssessmentLanguage: string
{
    case C = 'C';
    case C_PLUS_PLUS = 'C++';
    case JAVA = 'Java';
    case SCALA = 'Scala';
    case KOTLIN = 'Kotlin';
    case SWIFT = 'Swift';
    case PYTHON = 'Python';
    case C_SHARP = 'C#';
    case PHP = 'PHP';
    case JS = 'JS';
    case TS = 'TS';
    case GO = 'GO';
    case RUST = 'RUST';
    case MATLAB = 'MATLAB';
}
