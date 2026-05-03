<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Enums\ApplicationStatus;

class Application extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'candidate_id',
        'job_id',
        'ai_match_score'
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationsStatus::class,
        ];
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }
    
    public function job()
    {
        return $this->belongsTo(JobPost::class, 'job_id');
    }
}