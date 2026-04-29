<?php

namespace App\Models;

use Parental\HasParent;

class Candidate extends User
{
    use HasParent;

    protected $fillable = [
        'resume_path',
        'docs_path',
        'skills',
        'experience_years',
    ];
}
