<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeCreateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'email' => ['required', 'email', Rule::unique('employees', 'email')],
            'cpf' => ['required', 'string', Rule::unique('employees', 'cpf')],
            'city' => ['required', 'string', 'max:150'],
            'state' => ['required', 'string', 'max:32'],
        ];
    }
}
