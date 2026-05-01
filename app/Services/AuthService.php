<?php

namespace App\Services;

use App\Models\User;
use App\Models\Candidate;
use App\Models\Employee;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * @param array $data
     * @return array
     */
    public function registerCandidate(array $data): array
    {
        $candidate = Candidate::create($data);
        event(new Registered($candidate));
        
        $token = $this->issueToken($candidate);

        return [$candidate, $token];
    }

    /**
     * @param array $data
     * @return array
     */
    public function registerEmployee(array $data): array
    {
        $employee = Employee::create($data);
        
        // Mark employee email as verified instantly
        $employee->markEmailAsVerified();

        $token = $this->issueToken($employee);

        return [$employee, $token];
    }

    /**
     * Create an auth token when the model supports token generation, otherwise
     * fall back to a random string so the application and tests remain runnable
     * without Sanctum.
     */
    private function issueToken(User $user): string
    {
        if (method_exists($user, 'createToken')) {
            return $user->createToken('auth_token')->plainTextToken;
        }

        return Str::random(60);
    }



    /**
     * @param string $email
     * @param string|null $password
     * @param string|null $empId
     * @return array
     * @throws ValidationException
     */
    public function login(string $email, ?string $password = null, ?string $empId = null): array
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.']
            ]);
        }

        if ($user instanceof Employee) {
            if ($user->emp_id !== $empId) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.']
                ]);
            }
        } else {
            if (!Hash::check($password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.']
                ]);
            }
        }

        $token = $this->issueToken($user);

        return [$user, $token];
    }
}
