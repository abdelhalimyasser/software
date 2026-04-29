<?php

namespace App\Models;

use Parental\HasParent;

class Candidate extends User
{
    use HasParent;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'birth_date',
        'email',
        'phone_number',
        'password',
        'role',
        'profile_picture_path',
        'resume_path',
        'docs_path',
        'skills',
        'experience_years',
    ];
}
