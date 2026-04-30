<?php

namespace App\Policies;

use App\Models\JobRequisition;
use App\Models\User;
use App\Models\HrAdmin;
use App\Models\DepartmentManager;
use App\Models\Candidate;
use App\Models\Enums\JobStatus;

class JobRequisitionPolicy
{
    public function viewAny(User $user): bool
    {
        return true; 
    }

    public function view(User $user, JobRequisition $jobRequisition): bool
    {
        if ($user instanceof Candidate) {
            return $jobRequisition->status === JobStatus::APPROVED;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user instanceof HrAdmin;
    }

    public function approve(User $user, JobRequisition $jobRequisition): bool
    {
        return $user instanceof DepartmentManager;
    }

    public function reject(User $user, JobRequisition $jobRequisition): bool
    {
        return $user instanceof DepartmentManager;
    }
}