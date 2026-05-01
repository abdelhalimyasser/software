<?php

namespace App\Http\Controllers;

use App\Models\JobRequisition;
use App\Models\Enums\JobStatus;
use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;


/**
 * Class JobRequisitionController
 * 
 * Controller for managing job requisitions.
 * 
 * @package App\Http\Controllers
 * @version 1.0
 * @since 05-01-2026
 * @author Abdelhalim Yasser
 */
class JobRequisitionController extends Controller
{
    /**
     * Display a listing of the job requisitions.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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


    /**
     * Store a newly created job requisition in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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


    /**
     * Display the specified job requisition.
     *
     * @param \App\Models\JobRequisition $job
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(JobRequisition $job): JsonResponse
    {
        Gate::authorize('view', $job);

        return response()->json([
            'job' => $job->load('creator')
        ], 200);
    }


    /**
     * Approve the specified job requisition.
     *
     * @param \App\Models\JobRequisition $job
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function approve(JobRequisition $job, Request $request): JsonResponse
    {
        Gate::authorize('approve', $job);

        $manager = $request->user();
        $manager->approveJobRequisition($job);

        return response()->json([
            'message' => 'Job requisition approved successfully.',
            'job' => $job->fresh()
        ], 200);
    }

    /**
     * Reject the specified job requisition.
     *
     * @param \App\Models\JobRequisition $job
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reject(JobRequisition $job, Request $request): JsonResponse
    {
        Gate::authorize('reject', $job);

        $manager = $request->user();
        $manager->rejectJobRequisition($job);

        return response()->json([
            'message' => 'Job requisition rejected.',
            'job' => $job->fresh()
        ], 200);
    }
}