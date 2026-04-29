<?php

namespace Tests\Unit\Services;

use App\Models\Candidate;
use App\Models\Employee;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use DatabaseMigrations;

    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = app(AuthService::class);
    }

    public function test_register_candidate_returns_user_and_token(): void
    {
        Event::fake();

        $data = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'birth_date' => '2000-01-01',
            'email' => 'jane@example.com',
            'phone_number' => '0123456789',
            'password' => 'password',
            'role' => 'CANDIDATE',
            'profile_picture_path' => 'profiles/jane.jpg',
            'resume_path' => 'resumes/jane.pdf',
            'docs_path' => 'documents/jane.zip',
            'skills' => ['PHP', 'Laravel'],
            'experience_years' => 3,
        ];

        [$user, $token] = $this->authService->registerCandidate($data);

        $this->assertInstanceOf(Candidate::class, $user);
        $this->assertSame('jane@example.com', $user->email);
        $this->assertSame('Jane', $user->first_name);
        $this->assertSame('Doe', $user->last_name);
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        Event::assertDispatched(Registered::class);
        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'role' => 'CANDIDATE',
        ]);
    }

    public function test_register_candidate_fires_registered_event(): void
    {
        Event::fake();

        $data = [
            'first_name' => 'Event',
            'last_name' => 'Test',
            'birth_date' => '1995-06-15',
            'email' => 'event@example.com',
            'phone_number' => '0111111111',
            'password' => 'password',
            'role' => 'CANDIDATE',
        ];

        $this->authService->registerCandidate($data);

        Event::assertDispatched(Registered::class, function ($event) {
            return $event->user->email === 'event@example.com';
        });
    }

    public function test_register_employee_returns_user_and_token(): void
    {
        Event::fake();

        $data = [
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john@example.com',
            'phone_number' => '0100000000',
            'password' => 'password',
            'role' => 'EMPLOYEE',
        ];

        [$user, $token] = $this->authService->registerEmployee($data);

        $this->assertInstanceOf(Employee::class, $user);
        $this->assertSame('john@example.com', $user->email);
        $this->assertNotNull($user->emp_id);
        $this->assertStringStartsWith('NH-EMP-', $user->emp_id);
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        Event::assertDispatched(Registered::class);
    }

    public function test_login_returns_user_and_token_for_valid_credentials(): void
    {
        User::create([
            'first_name' => 'Login',
            'last_name' => 'User',
            'birth_date' => '2000-01-01',
            'email' => 'user@example.com',
            'phone_number' => '0123456789',
            'password' => Hash::make('secret-password'),
            'role' => 'CANDIDATE',
        ]);

        [$user, $token] = $this->authService->login('user@example.com', 'secret-password');

        $this->assertSame('user@example.com', $user->email);
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function test_login_throws_validation_exception_for_invalid_credentials(): void
    {
        User::create([
            'first_name' => 'Login',
            'last_name' => 'User',
            'birth_date' => '2000-01-01',
            'email' => 'user@example.com',
            'phone_number' => '0123456789',
            'password' => Hash::make('correct-password'),
            'role' => 'CANDIDATE',
        ]);

        $this->expectException(ValidationException::class);

        $this->authService->login('user@example.com', 'wrong-password');
    }

    public function test_login_throws_exception_for_nonexistent_email(): void
    {
        $this->expectException(ValidationException::class);

        $this->authService->login('nonexistent@example.com', 'any-password');
    }
}
