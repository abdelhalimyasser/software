<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'phone_number' => 'sometimes|string|max:15|unique:users,phone_number,' . $userId,
            'skills' => 'sometimes|array',
            'experience_years' => 'sometimes|integer|min:0|max:50',
            'profile_picture' => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:2048',
            'resume' => 'sometimes|file|mimes:pdf,doc,docx|max:5120',
            'docs' => 'sometimes|file|mimes:pdf,png,jpg,zip|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            // First Name Validation Messages
            'first_name.string' => 'Your first name must be a string.',
            'first_name.max' => 'Your first name must not exceed 255 characters.',

            // Last Name Validation Messages
            'last_name.string' => 'Your last name must be a string.',
            'last_name.max' => 'Your last name must not exceed 255 characters.',

            // Phone Number Validation Messages
            'phone_number.string' => 'Your phone number must be a string.',
            'phone_number.max' => 'Your phone number must not exceed 15 characters.',
            'phone_number.unique' => 'The phone number you have used has been registered before.',

            // Skills Validation Messages
            'skills.array' => 'Skills must be an array.',

            // Experience Years Validation Messages
            'experience_years.integer' => 'Your experience years must be a number.',
            'experience_years.min' => 'Your experience years must be at least 0.',
            'experience_years.max' => 'Your experience years must not exceed 50.',

            // Profile Picture Validation Messages
            'profile_picture.image' => 'Your profile picture must be an image.',
            'profile_picture.mimes' => 'Your profile picture must be a file of type: jpeg, png, jpg, webp.',
            'profile_picture.max' => 'Your profile picture must not be greater than 2MB.',

            // Resume Validation Messages
            'resume.file' => 'Your resume must be a file.',
            'resume.mimes' => 'The resume must be a file of type: pdf, doc, docx.',
            'resume.max' => 'The resume must not be greater than 5MB.',

            // Documents Validation Messages
            'docs.file' => 'Your documents must be a file.',
            'docs.mimes' => 'The documents must be a file of type: pdf, png, jpg, zip.',
            'docs.max' => 'The documents must not be greater than 10MB.',
        ];
    }
}
