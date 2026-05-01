<?php

namespace App\Models;

use Parental\HasParent;

/**
 * Class DepartmentManager
 * 
 * Represents a department manager.
 * 
 * @package App\Models
 * @version 1.0
 * @since 28-04-2026
 * @author Abdelhalim Yasser
 */
class DepartmentManager extends Employee
{
    use HasParent;

    public function approveJobRequisition(JobRequisition $job)
    {
        $job->update([
            'status' => JobStatus::APPROVED,
            'status_updated_by' => $this->id 
        ]);
    }

    public function rejectJobRequisition(JobRequisition $job)
    {
        $job->update([
            'status' => JobStatus::REJECTED,
            'status_updated_by' => $this->id 
        ]);
    }
}
