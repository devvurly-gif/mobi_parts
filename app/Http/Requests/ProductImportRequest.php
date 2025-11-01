<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
            'category_id' => 'required|exists:categories,id',
            'skip_duplicates' => 'required|in:0,1,true,false',
            'update_existing' => 'required|in:0,1,true,false',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Please select a file to import.',
            'file.file' => 'The uploaded file is not valid.',
            'file.mimes' => 'The file must be a XLS, XLSX, or CSV file.',
            'file.max' => 'The file size must not exceed 10MB.',
            'category_id.required' => 'Please select a category for the products.',
            'category_id.exists' => 'The selected category does not exist.',
            'skip_duplicates.required' => 'The skip duplicates field is required.',
            'skip_duplicates.in' => 'The skip duplicates field must be true or false.',
            'update_existing.required' => 'The update existing field is required.',
            'update_existing.in' => 'The update existing field must be true or false.',
        ];
    }
}
