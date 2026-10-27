<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePoaActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can($this->poaActivity ? 'poa.edit' : 'poa.create') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'poa_process_id' => ['required', 'integer', 'exists:poa_processes,id'],
            'organizational_unit_id' => ['required', 'integer', 'exists:organizational_units,id'],
            'programmatic_structure_id' => ['nullable', 'integer', 'exists:programmatic_structures,id'],
            'number' => ['required', 'string', 'max:50', Rule::unique('poa_activities')->where('poa_process_id', $this->integer('poa_process_id'))->ignore($this->poaActivity)],
            'description' => ['required', 'string', 'max:4000'], 'objective' => ['required', 'string', 'max:4000'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:999'], 'unit_of_measure' => ['nullable', 'string', 'max:100'],
            'annual_target' => ['required', 'numeric', 'min:0'], 'annual_amount' => ['required', 'numeric', 'min:0'],
            'responsible_user_id' => ['required', 'integer', 'exists:users,id'],
            'months' => ['nullable', 'array'], 'months.*.month' => ['required', 'integer', 'between:1,12', 'distinct'], 'months.*.target' => ['required', 'numeric', 'min:0'], 'months.*.amount' => ['required', 'numeric', 'min:0'],
            'version' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
