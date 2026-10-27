<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetCertificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('certification.create')
            || $this->user()?->can('execution.import')
            || $this->user()?->can('modification.verify_budget')
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
            'poa_process_id' => ['nullable', 'integer', 'exists:poa_processes,id'],
            'poa_certification_id' => ['nullable', 'integer', 'exists:poa_certifications,id'],
            'certification_number' => ['required', 'string', 'max:50'],
            'certification_date' => ['required', 'date'],
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'total_amount' => ['required', 'numeric', 'gt:0'],
            'funding_source' => ['nullable', 'string', 'max:100'],
            'concept' => ['nullable', 'string', 'max:2000'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.line_number' => ['nullable', 'integer', 'min:1'],
            'lines.*.concept' => ['nullable', 'string', 'max:1000'],
            'lines.*.poa_activity_id' => ['nullable', 'integer', 'exists:poa_activities,id'],
            'lines.*.funding_source' => ['nullable', 'string', 'max:100'],
            'lines.*.amount' => ['required', 'numeric', 'gt:0'],
        ];
    }
}
