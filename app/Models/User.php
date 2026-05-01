<?php

namespace App\Models;

use App\Models\Enums\UserRole;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Parental\HasChildren;

/**
 * Class User
 *
 * The User class represents a user in the application and serves as the base model for different user roles such as
 * Candidate, Employee, HR Admin, Interviewer, Shadow Interviewer, and Department Manager.
 * It implements the MustVerifyEmail interface to ensure that users verify their email addresses.
 * The class uses several traits to provide functionality for factory creation, notifications, UUIDs, and handling child models based on user roles.
 *
 * @version 1.0
 * @since 28-04-2026
 * @author Abdelhalim Yasser
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasChildren;

    protected string $childColumn = 'role';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
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
    ];

    public function save(array $options = []): bool
    {
        if (empty($this->name) && ($this->first_name || $this->last_name)) {
            $this->name = trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
        }

        return parent::save($options);
    }

    /**
     * This array defines the possible child types for the User model,
     * allowing for polymorphic relationships with Candidate and Employee models.
     *
     * @var array|string[]
     */
    protected array $childTypes = [
        UserRole::CANDIDATE->value => Candidate::class,
        UserRole::EMPLOYEE->value => Employee::class,
        UserRole::HR_ADMIN->value => HrAdmin::class,
        UserRole::INTERVIEWER->value => Interviewer::class,
        UserRole::SHADOW_INTERVIEWER->value => ShadowInterviewer::class,
        UserRole::DEPARTMENT_MANAGER->value => DepartmentManager::class
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

    protected function firstName(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
            set: fn (string $value) => strtolower($value),
        );
    }

    protected function lastName(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
            set: fn (string $value) => strtolower($value),
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
            set: fn (string $value) => strtolower($value),
        );
    }

    protected function birthDate(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
            set: fn (string $value) => strtolower($value),
        );
    }

    protected function phoneNumber(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
            set: fn (string $value) => strtolower($value),
        );
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => strtolower($value),
            set: fn (string $value) => strtolower($value),
        );
    }

    protected function password(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => strtolower($value),
            set: fn (string $value) => strtolower($value),
        );
    }

    protected function role(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => strtolower($value),
            set: fn (string $value) => strtolower($value),
        );
    }

    protected function skills(): Attribute
    {
        return Attribute::make(
            get: fn (string|array $value) => ucfirst($value),
            set: fn (string|array $value) => strtolower($value),
        );
    }

    protected function experienceYears(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
            set: fn (string $value) => strtolower($value),
        );
    }   
    
    protected function profilePicturePath(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => strtolower($value),
            set: fn (string $value) => strtolower($value),
        );
    }

    protected function resumePath(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => strtolower($value),
            set: fn (string $value) => strtolower($value),
        );
    }

    protected function docsPath(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => strtolower($value),
            set: fn (string $value) => strtolower($value),
        );
    }
}
