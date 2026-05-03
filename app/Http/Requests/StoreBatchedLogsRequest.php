<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBatchedLogsRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'logs' => ['required', 'array', 'min:1'],
            'logs.*.event_type' => ['required', 'string'],
            'logs.*.occurred_at' => ['required', 'date_format:Y-m-d H:i:s.v'], // e.g. "2026-05-03 14:30:00.123"
            'logs.*.metadata' => ['nullable', 'array']
        ];
    }
}
