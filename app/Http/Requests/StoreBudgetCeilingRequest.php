<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetCeilingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('poa.approve') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'planning_period_id' => ['required', 'integer', 'exists:planning_periods,id'], 'source' => ['required', 'string', 'max:100'],
            'current_expenditure' => ['required', 'numeric', 'min:0'], 'investment_expenditure' => ['required', 'numeric', 'min:0'],
            'methodology' => ['required', 'string', 'max:4000'], 'units' => ['required', 'array', 'min:1'],
            'units.*.organizational_unit_id' => ['required', 'integer', 'exists:organizational_units,id', 'distinct'], 'units.*.amount' => ['required', 'numeric', 'min:0'], 'units.*.adjustment_reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
