<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBatchedLogsRequest;
use App\Models\AssessmentLog;
use Illuminate\Http\JsonResponse;

class AssessmentLogController extends Controller
{
    /**
     * Store high-throughput batched tracking logs (Anti-Cheat Monitoring)
     */
    public function storeBatch(StoreBatchedLogsRequest $request, int $attempt_id): JsonResponse
    {
        $logs = $request->validated('logs');

        // Preparing array for bulk insert
        $insertData = array_map(function ($log) use ($attempt_id) {
            return [
                'assessment_attempt_id' => $attempt_id,
                'event_type' => $log['event_type'],
                'metadata' => isset($log['metadata']) ? json_encode($log['metadata']) : null,
                'occurred_at' => $log['occurred_at'],
                'created_at' => now(),
            ];
        }, $logs);

        // High-performance bulk insertion (Bypasses Eloquent instantiation overhead)
        AssessmentLog::insert($insertData);

        return response()->json([
            'message' => count($insertData) . ' logs recorded successfully.'
        ], 201);
    }
}
