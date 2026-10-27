<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePoaActivityPhaseAllocationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('poa.edit') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'poa_process_id' => ['required', 'integer', 'exists:poa_processes,id'], 'priority' => ['nullable', 'integer', 'min:1'],
            'adjusted_amount' => ['required', 'numeric', 'min:0'], 'adjusted_target' => ['nullable', 'numeric', 'min:0'], 'justification' => ['required', 'string', 'max:2000'],
        ];
    }
}
