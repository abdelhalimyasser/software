<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorized by Gate in the Controller
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'department' => 'required|string|max:255',
            'experience_level' => 'required|integer|min:0',
            'location' => 'nullable|string|max:255',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:255',
        ];
    }
}
