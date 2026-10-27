<?php

namespace App\Http\Requests;

use App\Enums\PoaPhase;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePoaProcessRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('admin.parameters') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'planning_period_id' => ['required', 'integer', 'exists:planning_periods,id'],
            'phase' => ['required', Rule::enum(PoaPhase::class), Rule::unique('poa_processes')->where('planning_period_id', $this->integer('planning_period_id'))],
            'responsible_user_id' => ['required', 'integer', 'exists:users,id'],
            'opens_at' => ['nullable', 'date'],
            'closes_at' => ['nullable', 'date', 'after:opens_at'],
        ];
    }
}
