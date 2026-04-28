<?php

namespace App\Models;

use App\Models\Enums\UserRole;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Parental\HasChildren;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasUuids, HasChildren;

    protected string $childColumn = 'role';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'birth_date',
        'email',
        'phone_number',
        'username',
        'password',
        'role',
        'skills',
        'experience_years',
        'resume_path',
        'docs_Path',
    ];

    /**
     * This array defines the possible child types for the User model,
     * allowing for polymorphic relationships with Candidate and Employee models.
     *
     * @var array|string[]
     */
    protected array $childTypes = [
        UserRole::CANDIDATE->value => Candidate::class,
        UserRole::EMPLOYEE->value => Candidate::class,
        UserRole::HR_ADMIN->value => HrAdmin::class,
        UserRole::INTERVIEWER->value => Interviewer::class,
        UserRole::SHADOW_INTERVIEWER->value => ShadowInterviewer::class,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'skills' => 'array'
        ];
    }
}
