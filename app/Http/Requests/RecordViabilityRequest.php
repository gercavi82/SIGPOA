<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RecordViabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('modification.review') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'viability_report' => ['required', 'string', 'max:5000'],
        ];
    }
}
