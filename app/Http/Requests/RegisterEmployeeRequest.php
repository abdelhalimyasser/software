<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow only authenticated users with proper permissions in real app.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'email'        => 'required|string|email|max:255|unique:users,email',
            'password'     => 'required|string|min:8',
            'role'         => 'required|string',
            'phone_number' => 'nullable|string|max:15|unique:users,phone_number',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Please enter the first name of the employee.',
            'first_name.string' => 'First name must be a string.',
            'first_name.max' => 'First name must not exceed 255 characters.',

            'last_name.required' => 'Please enter the last name of the employee.',
            'last_name.string' => 'Last name must be a string.',
            'last_name.max' => 'Last name must not exceed 255 characters.',

            'email.required' => 'Please enter the employee email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',

            'password.required' => 'Please enter a password for the employee.',
            'password.min' => 'The password must be at least 8 characters long.',

            'role.required' => 'Please specify a role for the employee.',

            'phone_number.string' => 'Phone number must be a string.',
            'phone_number.max' => 'Phone number must not exceed 15 characters.',
            'phone_number.unique' => 'This phone number is already in use.',

            'profile_picture.image' => 'Profile picture must be an image file.',
            'profile_picture.mimes' => 'Profile picture must be jpeg, png, jpg or webp.',
            'profile_picture.max' => 'Profile picture must not exceed 2MB.',
        ];
    }
}

