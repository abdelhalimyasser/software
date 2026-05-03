<?php

namespace App\Models;

use App\Models\Enums\EventType; // الـ Enum بتاعك
use Illuminate\Database\Eloquent\Model;

class AssessmentLog extends Model
{
    // شيلنا الـ updated_at لأن اللوج بيتسجل مرة واحدة ومش بيتعدل
    public const UPDATED_AT = null;

    protected $fillable = [
        'assessment_attempt_id', // مربوط بمحاولة المرشح الحالية
        'event_type',            // نوع الحدث (FOUCUS_LOSS, TAB_SWITCH...)
        'metadata',              // أي تفاصيل زيادة (زي: فتح تاب ايه، غاب كام ثانية)
        'occurred_at'            // وقت الحدث بالمللي ثانية
    ];

    protected function casts(): array
    {
        return [
            'event_type' => EventType::class, //[cite: 18]
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function attempt()
    {
        return $this->belongsTo(AssessmentAttempt::class, 'assessment_attempt_id');
    }
}