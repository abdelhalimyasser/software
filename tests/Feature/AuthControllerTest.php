<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Models\Candidate;
use App\Models\Employee;
use App\Models\Enums\UserRole;
use Illuminate\Support\Facades\Hash;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\Authenticate::class);
    }

    public function test_register_candidate_endpoint_success()
    {
        \Illuminate\Support\Facades\Event::fake();
        \Illuminate\Support\Facades\Notification::fake();
        Storage::fake('local');
        Storage::fake('public');

        $payload = [
            'first_name' => 'Alice',
            'last_name' => 'Walker',
            'birth_date' => '1992-05-01',
            'email' => 'alice@example.com',
            'phone_number' => '1234567890',
            'username' => 'alicew',
            'password' => 'StrongP@ssw0rd99!!',
            'experience_years' => '3',
            'resume' => UploadedFile::fake()->create('resume.pdf', 100),
            'docs' => UploadedFile::fake()->create('docs.zip', 100),
            'profile_picture' => UploadedFile::fake()->create('avatar.jpg', 100)
        ];

        $response = $this->postJson('/api/v1/public/auth/register', $payload);

        $response->assertStatus(201);
        $response->assertJsonStructure(['message', 'user', 'token']);
        $this->assertDatabaseHas('users', ['email' => 'alice@example.com']);
    }

    public function test_register_candidate_validation_failure_missing_files()
    {
        \Illuminate\Support\Facades\Event::fake();
        \Illuminate\Support\Facades\Notification::fake();

        $payload = [
            'first_name' => 'Bob',
            'last_name' => 'NoFile',
            'birth_date' => '1992-05-01',
            'email' => 'bob@example.com',
            'phone_number' => '1234567890',
            'username' => 'bobn',
            'password' => 'StrongP@ssw0rd99!!',
            'experience_years' => '2'
        ];

        $response = $this->postJson('/api/v1/public/auth/register', $payload);

        $response->assertStatus(422);
    }

    public function test_login_endpoint_success_and_failure()
    {
        \Illuminate\Support\Facades\Event::fake();
        \Illuminate\Support\Facades\Notification::fake();

        $password = 'LoginPass1!';
        $user = Candidate::create([
            'first_name' => 'Login',
            'last_name' => 'User',
            'email' => 'loginuser@example.com',
            'password' => Hash::make($password),
            'role' => UserRole::CANDIDATE->value
        ]);

        // successful login
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'loginuser@example.com',
            'password' => $password
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['user', 'token']);

        // failed login
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'loginuser@example.com',
            'password' => 'WrongP@ssw0rd99!!'
        ]);

        $response->assertStatus(401);
    }

    public function test_register_employee_and_update_employee_endpoints()
    {
        \Illuminate\Support\Facades\Event::fake();
        \Illuminate\Support\Facades\Notification::fake();

        // Register employee. We must bypass middleware or use HR Admin since private route requires auth & HR_ADMIN
        $hrAdmin = \App\Models\HrAdmin::create([
            'first_name' => 'HR',
            'last_name' => 'Admin',
            'email' => 'hr@example.com',
            'password' => Hash::make('Password1!'),
            'role' => UserRole::HR_ADMIN->value
        ]);

        $payload = [
            'first_name' => 'Emp',
            'last_name' => 'Loyee',
            'email' => 'emp@example.com',
            'password' => 'Password1!',
            'role' => UserRole::EMPLOYEE->value,
            'emp_id' => 'EMP100'
        ];

        $response = $this->actingAs($hrAdmin)->postJson('/api/v1/private/auth/register-new-employee', $payload);

        // It might be 201 or 200 depending on the endpoint. Let's check status.
        $response->assertStatus(201);
        $response->assertJsonStructure(['message', 'employee', 'token']);

        $employeeId = $response->json('employee.id') ?? $response->json('id');

        // update employee requires hr admin
        $update = [
            'first_name' => 'EmpUpdated',
            'last_name' => 'LoyeeUpdated',
            'phone_number' => '0987654321'
        ];

        $response = $this->actingAs($hrAdmin)->putJson("/api/v1/private/auth/update-employee/{$employeeId}", $update);

        $response->assertStatus(200);
        $response->assertJsonFragment(['first_name' => 'EmpUpdated']);
    }
}
