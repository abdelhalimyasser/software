<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Class RegisterUserRequest
 *
 * This class is responsible for validating the incoming request data when a user tries to register.
 * It ensures that all required fields are present and meet the specified criteria, such as format and uniqueness.
 * The validation rules cover various fields including personal information, contact details, credentials, and file uploads.
 *
 * @property string $first_name
 * @property string $last_name
 * @property string $birth_date
 * @property string $email
 * @property string $phone_number
 * @property string $password
 * @property array|null $skills
 * @property string $experience_years
 * @property string $profile_picture
 * @property string $resume
 * @property string $docs
 *
 * @version 1.0
 * @since 29-04-2026
 * @author Abdelhalim Yasser
 */
class RegisterUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|date|before:-18 years',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone_number' => 'required|string|max:15|unique:users,phone_number',
            'password' => [
                'required',
                'string',
                'max:255',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
            'skills' => 'nullable|array',
            'experience_years' => 'required|integer|min:0|max:50',
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'resume' => 'required|file|mimes:pdf,doc,docx|max:5120',
            'docs' => 'required|file|mimes:pdf,png,jpg,zip|max:10240',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return string[]
     */
    public function message(): array
    {
        return [
            // First Name Validation Messages
            'first_name.required' => 'Please enter your first name.',
            'first_name.string' => 'Your first name must be a string.',
            'first_name.max' => 'Your first name must not exceed 255 characters.',

            // Last Name Validation Messages
            'last_name.required' => 'Please enter your last name.',
            'last_name.string' => 'Your last name must be a string.',
            'last_name.max' => 'Your last name must not exceed 255 characters.',

            // Birth Date Validation Messages
            'birth_date.required' => 'Please enter your birth date.',
            'birth_date.date' => 'Your birth date must be a valid date.',
            'birth_date.before' => 'You Must be more than 18 years old to register.',

            // Email Validation Messages
            'email.required' => 'Please enter your email address.',
            'email.string' => 'Your email must be a string.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'Your email must not exceed 255 characters.',
            'email.unique' => 'The Email you have used has been registeres before.',

            // Phone Number Validation Messages
            'phone_number.required' => 'Please enter your phone number.',
            'phone_number.string' => 'Your phone number must be a string.',
            'phone_number.max' => 'Your phone number must not exceed 15 characters.',
            'phone_number.unique' => 'The Phone Number you have used has been registeres before.',

            // Password Validation Messages
            'password.required' => 'Please enter a password.',
            'password.string' => 'Your password must be a string.',
            'password.min' => 'The password must be at least 8 characters long.',
            'password.max' => 'The password must not exceed 255 characters.',
            'password.mixed' => 'The password must contain at least one uppercase and one lowercase letter.',
            'password.numbers' => 'The password must contain at least one number.',
            'password.symbols' => 'The password must contain at least one symbol.',
            'password.uncompromised' => 'This password has appeared in a data leak. Please choose a more secure password.',

            // Skills Validation Messages
            'skills.array' => 'Skills must be an array.',

            // Experience Years Validation Messages
            'experience_years.integer' => 'Years of experience must be an integer.',
            'experience_years.min' => 'Years of experience cannot be negative.',
            'experience_years.max' => 'Years of experience cannot exceed 50.',

            // Profile Picture Validation Messages
            'profile_picture.required' => 'Please upload a profile picture.',
            'profile_picture.image' => 'Your profile picture must be an image.',
            'profile_picture.mimes' => 'Your profile picture must be a file of type: jpeg, png, jpg, webp.',
            'profile_picture.max' => 'Your profile picture must not be greater than 2MB.',

            // Resume Validation Messages
            'resume.required' => 'Please upload your resume.',
            'resume.file' => 'Your resume must be a file.',
            'resume.mimes' => 'The resume must be a file of type: pdf, doc, docx.',
            'resume.max' => 'The resume must not be greater than 5MB.',

            // Documents Validation Messages
            'docs.required' => 'please upload the required documents.',
            'docs.file' => 'Your documents must be a file.',
            'docs.mimes' => 'The documents must be a file of type: pdf, png, jpg, zip.',
            'docs.max' => 'The documents must not be greater than 10MB.',
        ];
    }
}
