<?php

namespace App\Models;

use App\Models\Enums\UserRole;
use Parental\HasChildren;
use Parental\HasParent;

class Employee extends User
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
}
