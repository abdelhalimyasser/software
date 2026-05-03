<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'duration_minutes',
        'pass_mark',
        'total_mark',
        'created_by',
        'cooldown_period',
        'distribution_rules',
        'stage',
        'moss_userid',      // الـ ID بتاع التكنيكال على MOSS
        'moss_language',    // لغة البرمجة (java, python, c, etc.) عشان MOSS يفهمها
        'moss_sensitivity',
    ];

    protected function casts(): array
    {
        return [
            'distribution_rules' => 'array',
            'stage' => AssessmentStage::class,
        ];
    }
}