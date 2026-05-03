<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Question;
use App\Models\Enums\AttemptStatus;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Carbon\Carbon;

class AssessmentService
{
    /**
     * Start a new assessment attempt and attach dynamic questions
     * 
     * @param Application $application
     * @param Assessment $assessment
     * @return AssessmentAttempt
     */
    public function startAttempt(Application $application, Assessment $assessment): AssessmentAttempt
    {
        // 1. Check for recent failed attempts
        $previousAttempt = AssessmentAttempt::where('application_id', $application->id)
            ->where('assessment_id', $assessment->id)
            // Assuming we only care about FINISHED attempts to calculate cooldown
            // The exact statuses would depend on Enums, assuming "FAILED" or similar
            ->orderBy('completed_at', 'desc')
            ->first();

        $isRetake = false;

        if ($previousAttempt && $previousAttempt->completed_at) {
            $cooldownHours = $assessment->cooldown_period ?? 0;
            $cooldownEndTime = Carbon::parse($previousAttempt->completed_at)->addHours($cooldownHours);

            if (now()->lessThan($cooldownEndTime)) {
                throw new HttpException(403, "Cooldown period active. You cannot retake this assessment until " . $cooldownEndTime->toDateTimeString());
            }

            $isRetake = true;
        }

        return DB::transaction(function () use ($application, $assessment, $isRetake) {
            // Create attempt
            $attempt = AssessmentAttempt::create([
                'application_id' => $application->id,
                'assessment_id' => $assessment->id,
                'status' => 'IN_PROGRESS', // Or your Enum equivalent like AttemptStatus::IN_PROGRESS
                'started_at' => now(),
            ]);

            $rules = $assessment->distribution_rules ?? []; 

            if ($isRetake && $previousAttempt->score !== null) {
                $score = $previousAttempt->score;
                // If they scored very poorly, reduce hard and medium questions
                if ($score < 40) {
                    if (isset($rules['HARD']) && $rules['HARD'] > 0) {
                        $rules['HARD']--;
                        $rules['BASIC'] = ($rules['BASIC'] ?? 0) + 1;
                    }
                } 
                // If they scored okay but still failed, reduce hard, increase medium
                elseif ($score < $assessment->pass_mark) {
                    if (isset($rules['HARD']) && $rules['HARD'] > 0) {
                        $rules['HARD']--;
                        $rules['MEDIUM'] = ($rules['MEDIUM'] ?? 0) + 1;
                    }
                }
            }

            $pivotRecords = [];

            // Fetch dynamic random questions based on adjusted rules
            foreach ($rules as $difficulty => $count) {
                if ($count <= 0) continue;

                $questions = Question::where('difficulty_level', $difficulty)
                    ->inRandomOrder()
                    ->limit($count)
                    ->pluck('id');
                
                foreach ($questions as $questionId) {
                    $pivotRecords[$questionId] = [
                        'candidate_answer' => null,
                        'is_correct' => null,
                        'earned_mark' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Attach batch to pivot (attempt_questions)
            if (!empty($pivotRecords)) {
                $attempt->questions()->attach($pivotRecords);
            }

            return $attempt;
        });
    }
}
