<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MossWebhookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Add middleware authorization in route
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'attempt_id' => ['required', 'exists:assessment_attempts,id'],
            'plagiarism_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'moss_report_url' => ['required', 'url'],
        ];
    }
}
