<?php

namespace App\Http\Requests;

use App\Enums\ModificationType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePoaModificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('modification.create') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'number' => ['required', 'string', 'max:50', Rule::unique('poa_modification_requests', 'number')->ignore($this->poaModificationRequest)],
            'type' => ['required', Rule::enum(ModificationType::class)],
            'poa_process_id' => ['required', 'integer', 'exists:poa_processes,id'],
            'organizational_unit_id' => ['required', 'integer', 'exists:organizational_units,id'],
            'justification' => ['required', 'string', 'max:4000'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.source_activity_id' => ['nullable', 'integer', 'exists:poa_activities,id'],
            'lines.*.destination_activity_id' => ['nullable', 'integer', 'exists:poa_activities,id'],
            'lines.*.debit_amount' => ['required', 'numeric', 'min:0'],
            'lines.*.credit_amount' => ['required', 'numeric', 'min:0'],
            'lines.*.description' => ['required', 'string', 'max:2000'],
            'lines.*.months' => ['nullable', 'array'],
            'lines.*.months.*.month' => ['required', 'integer', 'between:1,12'],
            'lines.*.months.*.debit_amount' => ['required', 'numeric', 'min:0'],
            'lines.*.months.*.credit_amount' => ['required', 'numeric', 'min:0'],
            'version' => ['nullable', 'integer', 'min:1'],
        ];

        if ($this->isMethod('post') && ! $this->poaModificationRequest) {
            $rules['document'] = ['nullable', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:10240'];
            $rules['document_type'] = ['nullable', 'string', 'max:100'];
        }

        return $rules;
    }
}
