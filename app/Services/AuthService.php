<?php

namespace App\Services;

use App\Models\User;
use App\Models\Candidate;
use App\Models\Employee;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    // Helper function to send email then create token for each user
    private function register(User $user): array
    {
        event(new Registered($user));

        $token = $user->createToken('auth_token')->plainTextToken;

        return [$user, $token];
    }

    /**
     * @param array $data
     * @return array
     */
    public function registerCandidate(array $data): array
    {
        return $this->register(Candidate::create($data));
    }

    /**
     * @param array $data
     * @return array
     */
    public function registerEmployee(array $data): array
    {
        return $this->register(Employee::create($data));
    }

    /**
     * @param string $email
     * @param string $password
     * @return array
     * @throws ValidationException
     */
    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password))
        {
            throw ValidationException::withMessages([
                    'email' => [
                        'The provided credentials are incorrect.'
                    ]
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [$user, $token];
    }
}
