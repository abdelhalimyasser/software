<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\MossWebhookRequest;
use App\Models\AssessmentAttempt;
use Illuminate\Http\JsonResponse;

class WebhookController extends Controller
{
    /**
     * Handle the MOSS result from Node.js microservice
     */
    public function mossResults(MossWebhookRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        $attempt = AssessmentAttempt::findOrFail($data['attempt_id']);
        
        $attempt->update([
            'plagiarism_score' => $data['plagiarism_score'],
            'moss_report_url' => $data['moss_report_url'],
        ]);

        return response()->json([
            'message' => 'Assessment attempt updated successfully with MOSS results.',
            'data' => $attempt
        ]);
    }
}
