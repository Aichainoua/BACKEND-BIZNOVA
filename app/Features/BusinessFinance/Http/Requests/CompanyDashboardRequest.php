<?php

namespace App\Features\BusinessFinance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyDashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year' => ['sometimes', 'required', 'integer', 'between:2000,32767'],
        ];
    }
}
