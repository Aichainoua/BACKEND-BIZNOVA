<?php

namespace App\Features\BusinessFinance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFinancialOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'operation_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
        ];
    }
}
