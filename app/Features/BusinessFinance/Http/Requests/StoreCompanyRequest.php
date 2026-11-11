<?php

namespace App\Features\BusinessFinance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sector_id' => ['required', 'integer', 'exists:sectors,id'],
            'name' => ['required', 'string', 'max:150'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'opening_balance_date' => ['required', 'date'],
        ];
    }
}
