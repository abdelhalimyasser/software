<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\RegisterUserRequest;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class RegisterUserRequestTest extends TestCase
{
    use DatabaseMigrations;

    private function rules(): array
    {
        $request = new RegisterUserRequest();
        return $request->rules();
    }

    public function test_rules_require_all_mandatory_fields(): void
    {
        $rules = $this->rules();

        $requiredFields = [
            'first_name', 'last_name', 'birth_date',
            'email', 'phone_number', 'password',
            'experience_years', 'profile_picture', 'resume', 'docs',
        ];

        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $rules, "Missing rule for field: $field");
        }
    }

    public function test_birth_date_requires_minimum_18_years(): void
    {
        $rules = $this->rules();

        $this->assertStringContainsString('before:-18 years', $rules['birth_date']);
    }

    public function test_email_must_be_unique(): void
    {
        $rules = $this->rules();

        $this->assertStringContainsString('unique:users,email', $rules['email']);
    }

    public function test_phone_number_must_be_unique(): void
    {
        $rules = $this->rules();

        $this->assertStringContainsString('unique:users,phone_number', $rules['phone_number']);
    }

    public function test_skills_is_optional_array(): void
    {
        $rules = $this->rules();

        $this->assertSame('nullable|array', $rules['skills']);
    }

    public function test_resume_accepts_pdf_doc_docx(): void
    {
        $rules = $this->rules();

        $this->assertStringContainsString('mimes:pdf,doc,docx', $rules['resume']);
    }

    public function test_docs_accepts_pdf_png_jpg_zip(): void
    {
        $rules = $this->rules();

        $this->assertStringContainsString('mimes:pdf,png,jpg,zip', $rules['docs']);
    }

    public function test_profile_picture_accepts_image_types(): void
    {
        $rules = $this->rules();

        $this->assertStringContainsString('mimes:jpeg,png,jpg,webp', $rules['profile_picture']);
    }
}
