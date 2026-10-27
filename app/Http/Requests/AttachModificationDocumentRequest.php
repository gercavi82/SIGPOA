<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AttachModificationDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('modification.create') || $this->user()?->can('modification.review');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'document' => ['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:10240'],
            'document_type' => ['required', 'string', 'max:100'],
        ];
    }
}
