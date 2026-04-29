<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Adjust authorization logic as needed (e.g., only admins can update employees)
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $employeeId = $this->route('id') ?? $this->route('user');

        return [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $employeeId,
            'password' => 'sometimes|string|min:8',
            'role' => 'sometimes|string',
            'emp_id' => 'sometimes|string|unique:users,emp_id,' . $employeeId,
            'phone_number' => 'sometimes|string|max:15|unique:users,phone_number,' . $employeeId,
            'profile_picture' => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.string' => 'First name must be a string.',
            'first_name.max' => 'First name must not exceed 255 characters.',

            'last_name.string' => 'Last name must be a string.',
            'last_name.max' => 'Last name must not exceed 255 characters.',

            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',

            'password.min' => 'The password must be at least 8 characters long.',

            'emp_id.unique' => 'This employee id is already in use.',

            'phone_number.max' => 'Phone number must not exceed 15 characters.',
            'phone_number.unique' => 'This phone number is already in use.',

            'profile_picture.image' => 'Profile picture must be an image file.',
            'profile_picture.mimes' => 'Profile picture must be jpeg, png, jpg or webp.',
            'profile_picture.max' => 'Profile picture must not exceed 2MB.',
        ];
    }
}

