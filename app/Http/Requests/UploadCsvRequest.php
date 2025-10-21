<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadCsvRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'csv' => ['required', 'file', 'mimes:csv,txt'],
        ];
    }
}
