<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class LoginUserRequest extends FormRequest
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
        return [
            'email' => 'required|string|email|exists:users,email',
            'password' => 'required_without:emp_id|string',
            'emp_id' => 'required_without:password|string',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Your email is required.',
            'email.string' => 'Invalid Email or Password, Please Check your Inputs!',
            'email.email' => 'Invalid Email or Password, Please Check your Inputs!',
            'email.max' => 'Invalid Email or Password, Please Check your Inputs!',
            'email.exists' => 'Invalid Email or Password, Please Check your Inputs!',

            'password.required' => 'Your password is required.',
            'password.string' => 'Invalid Email or Password, Please Check your Inputs!',
            'password.max' => 'Invalid Email or Password, Please Check your Inputs!',
            'password.min' => 'Invalid Email or Password, Please Check your Inputs!',
            'password.mixedCase' => 'Invalid Email or Password, Please Check your Inputs!',
            'password.numbers' => 'Invalid Email or Password, Please Check your Inputs!',
            'password.symbols' => 'Invalid Email or Password, Please Check your Inputs!',
        ];
    }
}
