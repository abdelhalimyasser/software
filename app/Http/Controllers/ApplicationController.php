<?php

namespace App\Http\Controllers;

use App\Models\JobPost;
use App\Models\Application;
use App\Models\Enums\ApplicationStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ApplicationController extends Controller
{
    public function apply(Request $request, JobPost $job): JsonResponse
    {
        /** @var \App\Models\Candidate $user */
        $user = $request->user();

        if ($user->role->value !== 'CANDIDATE') {
            return response()->json(['error' => 'Only candidates can apply for jobs.'], 403);
        }

        $alreadyApplied = Application::where('candidate_id', $user->id)
                                     ->where('job_id', $job->id)
                                     ->exists();

        if ($alreadyApplied) {
            return response()->json(['error' => 'You have already applied for this job.'], 400);
        }

        $matchScore = 0;
        if (!empty($job->skills) && !empty($user->skills)) {
            $candidateSkills = array_map('strtolower', $user->skills);
            $jobSkills = array_map('strtolower', $job->skills);
            $commonSkills = array_intersect($jobSkills, $candidateSkills);
            
            if (count($jobSkills) > 0) {
                $matchScore = (count($commonSkills) / count($jobSkills)) * 100;
            }
        }

        $application = Application::create([
            'candidate_id' => $user->id,
            'job_id' => $job->id,
            'status' => ApplicationStatus::APPLIED,
            'ai_match_score' => round($matchScore, 2)
        ]);

        return response()->json([
            'message' => 'Application submitted successfully!',
            'application' => $application
        ], 201);
    }
}