<?php

namespace App\Features\BusinessFinance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFinancialOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'amount' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'operation_date' => ['sometimes', 'required', 'date'],
            'description' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
