<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReconcileBatchRowRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('execution.validate')
            || $this->user()?->can('execution.import')
            || false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'poa_activity_id' => ['required', 'integer', 'exists:poa_activities,id'],
            'budget_certification_id' => ['nullable', 'integer', 'exists:budget_certifications,id'],
        ];
    }
}
