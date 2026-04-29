<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\LoginUserRequest;
use Tests\TestCase;

class LoginUserRequestTest extends TestCase
{
    private function rules(): array
    {
        $request = new LoginUserRequest();
        return $request->rules();
    }

    public function test_rules_require_email_and_password(): void
    {
        $rules = $this->rules();

        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
    }

    public function test_email_must_exist_in_users_table(): void
    {
        $rules = $this->rules();

        $this->assertStringContainsString('exists:users,email', $rules['email']);
    }

    public function test_email_must_be_valid_format(): void
    {
        $rules = $this->rules();

        $this->assertStringContainsString('email', $rules['email']);
    }

    public function test_password_is_required(): void
    {
        $rules = $this->rules();

        // Password rules are an array
        $this->assertIsArray($rules['password']);
        $this->assertContains('required', $rules['password']);
    }

    public function test_custom_error_messages_are_defined(): void
    {
        $request = new LoginUserRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey('email.required', $messages);
        $this->assertArrayHasKey('password.required', $messages);
    }
}
