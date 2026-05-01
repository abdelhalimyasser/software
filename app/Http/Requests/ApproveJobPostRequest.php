<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveJobPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorized by Gate in the Controller
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|string|max:1000',
        ];
    }
}
