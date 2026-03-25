<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CsvUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'csv_file' => [
                'required',
                'file',
                'mimes:csv',
                'max:2048',
                function ($attribute, $value, $fail) {
                    if (strtolower($value->getClientOriginalExtension()) !== 'csv') {
                        $fail('The file must have a .csv extension.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'csv_file.required' => 'Please select a CSV file to upload.',
            'csv_file.mimes' => 'The file must be a CSV file (.csv).',
            'csv_file.max' => 'The file must not exceed 2MB.',
        ];
    }
}
