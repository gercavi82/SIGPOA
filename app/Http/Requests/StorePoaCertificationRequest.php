<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePoaCertificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('certification.create') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'poa_process_id' => ['required', 'integer', 'exists:poa_processes,id'], 'organizational_unit_id' => ['required', 'integer', 'exists:organizational_units,id'], 'number' => ['required', 'string', 'max:50', 'unique:poa_certifications,number'], 'subject' => ['required', 'string', 'max:2000'],
            'details' => ['required', 'array', 'min:1'], 'details.*.poa_activity_id' => ['required', 'integer', 'exists:poa_activities,id'], 'details.*.description' => ['nullable', 'string', 'max:1000'], 'details.*.amount' => ['required', 'numeric', 'gt:0'],
            'document' => ['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:10240'], 'document_type' => ['required', 'string', 'max:100'],
        ];
    }
}
