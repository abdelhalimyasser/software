<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\AuthService;
use App\Models\Candidate;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_candidate_creates_user_and_returns_token()
    {
        \Illuminate\Support\Facades\Event::fake();
        $service = new AuthService();

        $data = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'birth_date' => '1990-01-01',
            'email' => 'jane@example.com',
            'password' => 'Password1!',
            'role' => \App\Models\Enums\UserRole::CANDIDATE->value
        ];

        [$user, $token] = $service->registerCandidate($data);

        $this->assertInstanceOf(Candidate::class, $user);
        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
        $this->assertIsString($token);
    }

    public function test_register_employee_creates_user_and_returns_token()
    {
        \Illuminate\Support\Facades\Event::fake();
        $service = new AuthService();

        $data = [
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john@example.com',
            'password' => 'Password1!',
            'role' => \App\Models\Enums\UserRole::EMPLOYEE->value,
            'emp_id' => 'EMP001'
        ];

        [$user, $token] = $service->registerEmployee($data);

        $this->assertInstanceOf(Employee::class, $user);
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
        $this->assertIsString($token);
    }

    public function test_login_with_correct_credentials_returns_user_and_token()
    {
        $password = 'Secret123!';
        $user = Candidate::create([
            'first_name' => 'Log',
            'last_name' => 'In',
            'email' => 'login@example.com',
            'password' => Hash::make($password),
            'role' => \App\Models\Enums\UserRole::CANDIDATE->value
        ]);

        $service = new AuthService();

        [$found, $token] = $service->login('login@example.com', $password);

        $this->assertEquals($user->id, $found->id);
        $this->assertIsString($token);
    }

    public function test_login_with_wrong_credentials_throws_validation_exception()
    {
        $this->expectException(ValidationException::class);

        $service = new AuthService();

        $service->login('nonexistent@example.com', 'badpassword');
    }
}


