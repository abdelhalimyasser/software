<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Enums\QuestionCategory;
use App\Models\Enums\QuestionDifficulty;

class Question extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    use HasFactory;

    protected $fillable = [
        'question',
        'description',
        'category',
        'recommended_base_answer',
        'test_cases',
        'difficulty',
    ];

    protected function casts(): array
    {
        return [
            'category' => QuestionCategory::class,
            'difficulty' => QuestionDifficulty::class,
            'test_cases' => 'json',
        ];
    }

    public function base_answer()
    {
        return $this->hasOne(BaseAnswer::class);
    }

    public function test_cases()
    {
        return $this->hasMany(TestCase::class);
    }    
}