<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RecordEsigefReformRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('modification.verify_budget') || $this->user()?->can('modification.review');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'esigef_reform_number' => ['required', 'string', 'max:100'],
            'reform_cur' => ['required', 'string', 'max:100'],
        ];
    }
}
