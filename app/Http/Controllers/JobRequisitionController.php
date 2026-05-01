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
            if ($user->experience_years < ($job->experience_level - 1)) {
                return false;
            }

            if (!empty($job->skills) && !empty($user->skills)) {
                $candidateSkills = array_map('strtolower', $user->skills);
                $jobSkills = array_map('strtolower', $job->skills);

                $commonSkills = array_intersect($jobSkills, $candidateSkills);
                $jobSkillsCount = count($jobSkills);

                if ($jobSkillsCount > 0) {
                    $matchPercentage = (count($commonSkills) / $jobSkillsCount) * 100;
                    if ($matchPercentage < 80) {
                        return false;
                    }
                }
            } else {
                return false;
            }

            return true;
        })->values();

        return response()->json([
            'message' => 'Here are the best jobs matching your profile.',
            'jobs' => $matchedJobs
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', JobPost::class);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'department' => 'required|string|max:255',
            'experience_level' => 'required|integer',
        ]);

        $data['created_by'] = $request->user()->id;
        $data['status'] = JobStatus::PENDING;

        $job = JobPost::create($data);

        $managers = User::where('role', UserRole::DEPARTMENT_MANAGER->value)->get();
        Notification::send($managers, new JobRequiresApprovalNotification($job));

        return response()->json([
            'message' => 'Job created. Managers have been notified.',
            'job' => $job
        ], 201);
    }

    public function show(JobPost $job): JsonResponse
    {
        Gate::authorize('view', $job);

        return response()->json([
            'job' => $job->load('creator')
        ], 200);
    }

    public function approve(JobPost $job, Request $request): JsonResponse
    {
        Gate::authorize('approve', $job);

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $manager = $request->user();
        $manager->approveJobRequisition($job, $request->reason);

        $job->creator->notify(new JobStatusUpdatedNotification($job));

        return response()->json([
            'message' => 'Job approved successfully. HR has been notified.',
            'job' => $job->fresh()
        ], 200);
    }

    public function reject(JobPost $job, Request $request): JsonResponse
    {
        Gate::authorize('reject', $job);

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $manager = $request->user();
        $manager->rejectJobRequisition($job, $request->reason);

        $job->creator->notify(new JobStatusUpdatedNotification($job));

        return response()->json([
            'message' => 'Job rejected. HR has been notified.',
            'job' => $job->fresh()
        ], 200);
    }
}
