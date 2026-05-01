<?php

namespace App\Http\Controllers;

use App\Models\JobPost;
use App\Models\Enums\JobStatus;
use App\Models\Candidate;
use App\Models\User;
use App\Models\Enums\UserRole;
use App\Notifications\JobRequiresApprovalNotification;
use App\Notifications\JobStatusUpdatedNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;

use App\Http\Requests\StoreJobPostRequest;
use App\Http\Requests\ApproveJobPostRequest;
use App\Http\Requests\RejectJobPostRequest;

/**
 * Class JobRequisitionController
 *
 * This controller handles the job requisition process, including creating new job posts, viewing available jobs,
 * and approving or rejecting job requisitions by department managers.
 * The controller ensures that only authorized users can perform certain actions and sends notifications to relevant parties when job requisitions are created or updated.
 *
 * @package App\Http\Controllers
 * @version 1.0
 * @since 01-05-2026
 * @author Abdelahalim Yasser
 */
class JobRequisitionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', JobPost::class);

        $user = $request->user();

        if (!$user instanceof Candidate) {
            $jobs = JobPost::with('creator')->latest()->paginate(10);
            return response()->json(['jobs' => $jobs], 200);
        }

        $approvedJobs = JobPost::where('status', JobStatus::APPROVED)->get();

        $matchedJobs = $approvedJobs->filter(function ($job) use ($user) {
            $userExperience = (int) $user->experience_years;
            if ($userExperience < ($job->experience_level - 1)) {
                return false;
            }

            if (empty($job->skills)) {
                return true; // No skills required, it's a match regarding skills
            }

            if (empty($user->skills)) {
                return false; // Job requires skills, but user has none
            }

            $candidateSkills = array_map('strtolower', $user->skills);
            $jobSkills = array_map('strtolower', $job->skills);

            $commonSkills = array_intersect($jobSkills, $candidateSkills);
            $jobSkillsCount = count($jobSkills);

            $matchPercentage = (count($commonSkills) / $jobSkillsCount) * 100;
            if ($matchPercentage < 80) {
                return false;
            }

            return true;
        })->values();

        return response()->json(['jobs' => $matchedJobs], 200);
    }

    public function store(StoreJobPostRequest $request): JsonResponse
    {
        Gate::authorize('create', JobPost::class);

        $validatedData = $request->validated();
        $validatedData['created_by'] = $request->user()->id;
        $validatedData['status'] = JobStatus::PENDING;

        $job = JobPost::create($validatedData);

        // Notify all department managers
        $departmentManagers = User::where('role', UserRole::DEPARTMENT_MANAGER->value)->get();
        Notification::send($departmentManagers, new JobRequiresApprovalNotification($job));

        return response()->json([
            'message' => 'Job created successfully. Waiting for department manager approval.',
            'job' => $job
        ], 201);
    }

    public function show(JobPost $job): JsonResponse
    {
        Gate::authorize('view', $job);

        return response()->json(['job' => $job->load(['creator', 'statusUpdater'])], 200);
    }

    public function approve(ApproveJobPostRequest $request, JobPost $job): JsonResponse
    {
        Gate::authorize('approve', $job);

        $reason = $request->validated('reason');

        // Assuming the authenticated user is a DepartmentManager
        /** @var \App\Models\DepartmentManager $manager */
        $manager = $request->user();
        $manager->approveJobRequisition($job, $reason);

        // Notify the creator
        if ($job->creator) {
            Notification::send($job->creator, new JobStatusUpdatedNotification($job));
        }

        return response()->json([
            'message' => 'Job approved successfully. HR has been notified.',
            'job' => $job->fresh()
        ], 200);
    }

    public function reject(RejectJobPostRequest $request, JobPost $job): JsonResponse
    {
        Gate::authorize('reject', $job);

        $reason = $request->validated('reason');

        // Assuming the authenticated user is a DepartmentManager
        /** @var \App\Models\DepartmentManager $manager */
        $manager = $request->user();
        $manager->rejectJobRequisition($job, $reason);

        // Notify the creator
        if ($job->creator) {
            Notification::send($job->creator, new JobStatusUpdatedNotification($job));
        }

        return response()->json([
            'message' => 'Job rejected. HR has been notified.',
            'job' => $job->fresh()
        ], 200);
    }
}
