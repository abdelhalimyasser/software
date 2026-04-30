<?php

namespace App\Http\Controllers;

use App\Models\JobRequisition;
use App\Models\Enums\JobStatus;
use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class JobRequisitionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', JobRequisition::class);

        $user = $request->user();
        $query = JobRequisition::query();

        if ($user instanceof Candidate) {
            $query->where('status', JobStatus::APPROVED);
        }

        $jobs = $query->with('creator')->latest()->paginate(10);

        return response()->json([
            'message' => 'Jobs retrieved successfully.',
            'jobs' => $jobs
        ], 200);
    }


    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', JobRequisition::class);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'department' => 'required|string|max:255',
        ]);

        $data['created_by'] = $request->user()->id;
        $data['status'] = JobStatus::PENDING;

        $job = JobRequisition::create($data);

        return response()->json([
            'message' => 'Job requisition created and pending approval.',
            'job' => $job
        ], 201);
    }


    public function show(JobRequisition $job): JsonResponse
    {
        Gate::authorize('view', $job);

        return response()->json([
            'job' => $job->load('creator')
        ], 200);
    }


    public function approve(JobRequisition $job, Request $request): JsonResponse
    {
        Gate::authorize('approve', $job);

        $job->update([
            'status' => JobStatus::APPROVED,
            'status_updated_by' => $request->user()->id
        ]);

        return response()->json([
            'message' => 'Job requisition approved successfully.',
            'job' => $job->fresh()
        ], 200);
    }


    public function reject(JobRequisition $job, Request $request): JsonResponse
    {
        Gate::authorize('reject', $job);

        $job->update([
            'status' => JobStatus::REJECTED,
            'status_updated_by' => $request->user()->id
        ]);

        return response()->json([
            'message' => 'Job requisition rejected.',
            'job' => $job->fresh()
        ], 200);
    }
}