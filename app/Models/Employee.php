<?php

namespace App\Models;

use App\Models\Enums\UserRole;
use Parental\HasChildren;
use Parental\HasParent;

class Employee extends User
{
    use HasParent;

    protected $fillable = [
        'emp_id'
    ];

    protected static function booted(): void
    {
        static::creating(function (Employee $employee) {
            do {
                $generatedId = 'NH-EMP-' . date('Y') . '-' . str_pad((string) mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            } while (self::where('emp_id', $generatedId)->exists());

            $employee->emp_id = $generatedId;
        });
    }

    public function makeReferral(int $userId)
    {
        // Logic to create a referral for the given user ID
    }
}
