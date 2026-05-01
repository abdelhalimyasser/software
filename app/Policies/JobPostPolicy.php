<?php

namespace App\Policies;

use App\Models\JobPost;
use App\Models\User;
use App\Models\HrAdmin;
use App\Models\DepartmentManager;
use App\Models\Candidate;
use App\Models\Enums\JobStatus;

class JobPostPolicy
{
    public function viewAny(User $user): bool
    {
        return true; 
    }

    public function view(User $user, JobPost $jobPost): bool
    {
        if ($user instanceof Candidate) {
            return $jobPost->status === JobStatus::APPROVED;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user instanceof HrAdmin;
    }

    public function approve(User $user, JobPost $jobPost): bool
    {
        return $user instanceof DepartmentManager;
    }

    public function reject(User $user, JobPost $jobPost): bool
    {
        return $user instanceof DepartmentManager;
    }
}