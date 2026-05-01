<?php

namespace App\Models;

use Parental\HasParent;

/**
 * Class Candidate
 * 
 * Represents a candidate.
 * 
 * @package App\Models
 * @version 1.0
 * @since 28-04-2026
 * @author Abdelhalim Yasser
 */
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

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\CustomVerifyEmail);
    }
}
