<?php

namespace App\Features\BusinessFinance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sector_id' => ['sometimes', 'required', 'integer', 'exists:sectors,id'],
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'opening_balance' => ['sometimes', 'required', 'numeric', 'min:0'],
            'opening_balance_date' => ['sometimes', 'required', 'date'],
        ];
    }
}
