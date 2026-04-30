<?php

namespace App\Models;

use App\Models\Employee;
use Parental\HasParent;

abstract class AbstractInterviewer extends Employee
{
    use HasParent;
    public function attendInterview()
    {
        // Interview logic here
    }

    public function evaluateCandidate()
    {
        // Evaluation logic here
    }
}
