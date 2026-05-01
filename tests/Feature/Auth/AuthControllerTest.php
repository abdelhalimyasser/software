<?php

namespace Tests\Feature\Auth;

use App\Models\Candidate;
use App\Models\Employee;
use App\Models\Enums\UserRole;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use DatabaseMigrations;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }


    //  Candidate Registration
    public function test_candidate_register_endpoint_returns_created_user_and_token(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        Http::fake();
        Notification::fake();

        $response = $this->postJson('/api/v1/public/auth/register', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'birth_date' => '2000-01-01',
            'email' => 'jane@example.com',
            'phone_number' => '0100000000',
            'password' => 'Str0ng!Pass#99x',
            'skills' => ['PHP', 'Laravel'],
            'experience_years' => 4,
            'profile_picture' => UploadedFile::fake()->createWithContent(
                'profile.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+XrS8AAAAASUVORK5CYII=')
            ),
            'resume' => UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
            'docs' => UploadedFile::fake()->create('docs.zip', 100, 'application/zip'),
        ]);

        $response->assertCreated();
        $response->assertJsonFragment([
            'message' => 'Candidate registered successfully',
        ]);
        $response->assertJsonStructure(['message', 'user', 'token']);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'role' => 'CANDIDATE',
        ]);
    }

    public function test_candidate_register_without_required_fields_returns_422(): void
    {
        $response = $this->postJson('/api/v1/public/auth/register', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'first_name', 'last_name', 'birth_date',
            'email', 'phone_number', 'password',
            'experience_years', 'profile_picture', 'resume', 'docs',
        ]);
    }

    public function test_candidate_register_with_duplicate_email_returns_422(): void
    {
        Http::fake();

        $this->makeUser(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/v1/public/auth/register', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'birth_date' => '2000-01-01',
            'email' => 'taken@example.com',
            'phone_number' => '0100000099',
            'password' => 'Str0ng!Pass#99x',
            'experience_years' => 2,
            'profile_picture' => UploadedFile::fake()->createWithContent(
                'profile.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+XrS8AAAAASUVORK5CYII=')
            ),
            'resume' => UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
            'docs' => UploadedFile::fake()->create('docs.zip', 100, 'application/zip'),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_candidate_register_with_underage_birth_date_returns_422(): void
    {
        Http::fake();

        $response = $this->postJson('/api/v1/public/auth/register', [
            'first_name' => 'Young',
            'last_name' => 'User',
            'birth_date' => now()->subYears(15)->toDateString(),
            'email' => 'young@example.com',
            'phone_number' => '0100000099',
            'password' => 'Str0ng!Pass#99x',
            'experience_years' => 0,
            'profile_picture' => UploadedFile::fake()->createWithContent(
                'profile.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+XrS8AAAAASUVORK5CYII=')
            ),
            'resume' => UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
            'docs' => UploadedFile::fake()->create('docs.zip', 100, 'application/zip'),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['birth_date']);
    }

    public function test_candidate_register_endpoint_validates_required_documents(): void
    {
        Http::fake();

        $response = $this->postJson('/api/v1/public/auth/register', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'birth_date' => '2000-01-01',
            'email' => 'jane@example.com',
            'phone_number' => '0100000000',
            'password' => 'Str0ng!Pass#99x',
            'skills' => ['PHP', 'Laravel'],
            'experience_years' => 4,
            'profile_picture' => UploadedFile::fake()->createWithContent(
                'profile.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+XrS8AAAAASUVORK5CYII=')
            ),
            'resume' => UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
            // docs intentionally omitted
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['docs']);
    }

    //  Employee Registration
    public function test_employee_register_endpoint_returns_created_employee_and_token(): void
    {
        // Fake notifications to prevent verification email route error
        Notification::fake();
        $response = $this->postJson('/api/v1/private/auth/register-new-employee', [
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'role' => UserRole::EMPLOYEE->value,
            'phone_number' => '0100000001',
        ]);

        $response->assertCreated();
        $response->assertJsonFragment([
            'message' => 'Employee registered successfully',
        ]);
        $response->assertJsonStructure(['message', 'employee', 'id', 'token']);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'role' => 'EMPLOYEE',
        ]);
    }

    public function test_employee_register_without_required_fields_returns_422(): void
    {
        $response = $this->postJson('/api/v1/private/auth/register-new-employee', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'first_name', 'last_name', 'email', 'password', 'role',
        ]);
    }

    public function test_employee_register_with_duplicate_email_returns_422(): void
    {
        $this->makeUser(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/v1/private/auth/register-new-employee', [
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'taken@example.com',
            'password' => 'Password123!',
            'role' => UserRole::EMPLOYEE->value,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    //  Login
    public function test_login_endpoint_returns_user_and_token(): void
    {
        $this->makeUser([
            'email' => 'login@example.com',
            'password' => Hash::make('Password123!'),
            'role' => UserRole::CANDIDATE->value,
        ]);

        $response = $this->postJson('/api/v1/private/auth/login', [
            'email' => 'login@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['user', 'token']);
        $response->assertJsonPath('user.email', 'login@example.com');
    }

    public function test_login_with_invalid_credentials_returns_401(): void
    {
        $this->makeUser([
            'email' => 'login@example.com',
            'password' => Hash::make('Password123!'),
            'role' => UserRole::CANDIDATE->value,
        ]);

        $response = $this->postJson('/api/v1/private/auth/login', [
            'email' => 'login@example.com',
            'password' => 'WrongPass123!',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_with_nonexistent_email_returns_422(): void
    {
        $response = $this->postJson('/api/v1/private/auth/login', [
            'email' => 'nobody@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_login_with_wrong_password_returns_401(): void
    {
        $this->makeUser(['email' => 'login@example.com']);

        $response = $this->postJson('/api/v1/private/auth/login', [
            'email' => 'login@example.com',
            'password' => 'short',
        ]);

        $response->assertStatus(401);
    }

    //  Update Employee
    public function test_update_employee_endpoint_updates_the_employee_record(): void
    {
        $employee = $this->makeEmployee([
            'first_name' => 'Old',
            'last_name' => 'Name',
            'email' => 'employee@example.com',
            'phone_number' => '0100000002',
        ]);

        $response = $this->withoutMiddleware()->putJson(
            '/api/v1/private/auth/update-employee/' . $employee->id,
            [
                'first_name' => 'New',
                'last_name' => 'Name',
                'phone_number' => '0100000003',
            ]
        );

        $response->assertOk();
        $response->assertJsonFragment([
            'message' => 'Employee updated successfully',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $employee->id,
            'first_name' => 'New',
            'phone_number' => '0100000003',
        ]);
    }

    public function test_update_employee_returns_404_for_nonexistent_id(): void
    {
        $response = $this->withoutMiddleware()->putJson(
            '/api/v1/private/auth/update-employee/99999',
            ['first_name' => 'Ghost']
        );

        $response->assertStatus(404);
    }

    //  Update Profile
    public function test_update_profile_endpoint_updates_authenticated_user(): void
    {
        $user = $this->makeUser([
            'first_name' => 'Old',
            'last_name' => 'Candidate',
            'email' => 'profile@example.com',
            'phone_number' => '0100000004',
            'role' => UserRole::CANDIDATE->value,
        ]);

        $response = $this->actingAs($user)->withoutMiddleware()->postJson('/api/v1/profile/update', [
            'first_name' => 'Updated',
            'last_name' => 'Candidate',
            'phone_number' => '0100000005',
            'skills' => ['PHP', 'Testing'],
            'experience_years' => 5,
        ]);

        $response->assertOk();
        $response->assertJsonPath('user.first_name', 'Updated');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Updated',
            'phone_number' => '0100000005',
        ]);
    }

    public function test_update_profile_with_partial_data(): void
    {
        $user = $this->makeUser([
            'first_name' => 'Original',
            'last_name' => 'Name',
            'email' => 'partial@example.com',
            'phone_number' => '0100000006',
            'role' => UserRole::CANDIDATE->value,
        ]);

        $response = $this->actingAs($user)->withoutMiddleware()->postJson('/api/v1/profile/update', [
            'first_name' => 'Changed',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Changed',
            'last_name' => 'Name', // unchanged
        ]);
    }

    //  Helpers
    private function makeUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'first_name' => 'Test',
            'last_name' => 'User',
            'birth_date' => '2000-01-01',
            'email' => 'test@example.com',
            'phone_number' => '0100000000',
            'password' => Hash::make('Password123!'),
            'role' => UserRole::CANDIDATE->value,
            'profile_picture_path' => null,
        ], $overrides));
    }

    private function makeEmployee(array $overrides = []): Employee
    {
        return Employee::create(array_merge([
            'first_name' => 'Employee',
            'last_name' => 'User',
            'email' => 'emp@example.com',
            'phone_number' => '0100000002',
            'password' => Hash::make('Password123!'),
            'role' => UserRole::EMPLOYEE->value,
            'profile_picture_path' => null,
        ], $overrides));
    }
}
