<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Enums\AttemptStatus;

class AssessmentAttempt extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'application_id', // ربطناه بالتقديم بتاع المرشح
        'assessment_id', // الامتحان اللي بيمتحنه
        'score',
        'status',
        'started_at',
        'completed_at',
        'plagiarism_score', // نسبة الاقتباس اللي راجعة من MOSS (مثلاً 85%)
        'moss_report_url',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'status' => AttemptStatus::class,
        ];
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'attempt_questions')
                    ->withPivot('candidate_answer', 'is_correct', 'earned_mark');
    }
}