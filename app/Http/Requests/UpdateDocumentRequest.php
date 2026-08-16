<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->user()?->company_id;

        return [
            'title'       => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'folder_id'   => ['nullable', 'integer', Rule::exists('folders', 'id')->where('company_id', $companyId)],
            'is_archived' => ['nullable', 'boolean'],
            'metadata'    => ['nullable', 'array'],
        ];
    }
}
