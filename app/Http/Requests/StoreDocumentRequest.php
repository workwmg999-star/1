<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxMb = $this->user()?->company?->plan?->max_file_size_mb ?? 50;
        $companyId = $this->user()?->company_id;

        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'folder_id'   => ['nullable', 'integer', Rule::exists('folders', 'id')->where('company_id', $companyId)],
            'file'        => ['required_without:files', 'file', "max:{$maxMb}048", 'mimes:pdf,jpg,jpeg,png,webp'],
            'files'       => ['required_without:file', 'array'],
            'files.*'     => ['file', "max:{$maxMb}048", 'mimes:pdf,jpg,jpeg,png,webp'],
            'metadata'    => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required_without'  => 'Please upload a file or multiple page images.',
            'files.required_without' => 'Please upload a file or multiple page images.',
            'file.mimes'             => 'Only PDF, JPG, JPEG, PNG, and WEBP files are allowed.',
            'files.*.mimes'          => 'Only PDF, JPG, JPEG, PNG, and WEBP files are allowed.',
        ];
    }
}
