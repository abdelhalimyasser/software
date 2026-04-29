<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\RegisterEmployeeRequest;
use Tests\TestCase;

class RegisterEmployeeRequestTest extends TestCase
{
    private function rules(): array
    {
        return (new RegisterEmployeeRequest())->rules();
    }

    public function test_rules_require_employee_fields(): void
    {
        $rules = $this->rules();
        foreach (['first_name', 'last_name', 'email', 'password', 'role'] as $field) {
            $this->assertArrayHasKey($field, $rules);
            $this->assertStringContainsString('required', $rules[$field]);
        }
    }

    public function test_profile_picture_is_optional(): void
    {
        $this->assertStringContainsString('nullable', $this->rules()['profile_picture']);
    }

    public function test_email_must_be_unique(): void
    {
        $this->assertStringContainsString('unique:users,email', $this->rules()['email']);
    }
}
