<?php

namespace App\Models;

use Parental\HasParent;

class DepartmentManager extends Employee
{
    use HasParent;

    public function approveJobRequisition()
    {
        // Logic to approve a job requisition
    }
}
