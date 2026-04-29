<?php

namespace App\Models;

use App\Models\Employee;

abstract class AbstractInterviewer extends Employee
{
    public function attendInterview()
    {
        // Interview logic here
    }

    public function evaluateCandidate()
    {
        // Evaluation logic here
    }
}
