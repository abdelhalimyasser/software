<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Application;
use App\Services\AssessmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    protected AssessmentService $assessmentService;

    public function __construct(AssessmentService $assessmentService)
    {
        $this->assessmentService = $assessmentService;
    }

    /**
     * Start a new assessment attempt based on the Candidate application
     */
    public function startAttempt(Request $request, int $assessmentId): JsonResponse
    {
        // For simplicity, passing application_id in request. 
        // In real-world, might be inferred from auth()
        $request->validate(['application_id' => 'required|exists:applications,id']);

        $assessment = Assessment::findOrFail($assessmentId);
        $application = Application::findOrFail($request->application_id);

        try {
            $attempt = $this->assessmentService->startAttempt($application, $assessment);
            return response()->json([
                'message' => 'Assessment started successfully.',
                'data' => $attempt->load('questions')
            ], 201);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }

    /**
     * Submit an assessment attempt for grading and MOSS check.
     */
    public function submitAttempt(Request $request, int $attemptId): JsonResponse
    {
        $attempt = \App\Models\AssessmentAttempt::findOrFail($attemptId);
        
        // Optional: calculate score here
        // $attempt->update(['score' => rand(0, 100)]); 

        \App\Jobs\SubmitToMossJob::dispatch($attempt);

        return response()->json([
            'message' => 'Assessment submitted. MOSS check is running asynchronously.',
        ]);
    }
}
