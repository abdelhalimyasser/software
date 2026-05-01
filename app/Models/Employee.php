<?php

namespace App\Models;

use App\Models\Enums\UserRole;

use Parental\HasParent;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Class Employee
 * 
 * Represents an employee.
 * 
 * @package App\Models
 * @version 1.0
 * @since 28-04-2026
 * @author Abdelhalim Yasser
 */
class Employee extends User
{
    use HasParent;

    protected $table = 'users';

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
        'emp_id',
    ];

    public function save(array $options = []): bool
    {
        if (!$this->exists && empty($this->emp_id)) {
            do {
                $generatedId = 'NH-EMP-' . date('Y') . '-' . str_pad((string) mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            } while (self::where('emp_id', $generatedId)->exists());

            $this->emp_id = $generatedId;
        }

        return parent::save($options);
    }



    public function makeReferral(int $userId)
    {
        // Logic to create a referral for the given user ID
    }

    /**
     * Employees do not need email verification.
     * @return bool
     */
    public function hasVerifiedEmail(): bool
    {
        return true;
    }
}
