<?php

namespace App\Features\ProjectAnalysis\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
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
            'launch_date' => ['required', 'date'],
            'horizon_months' => ['sometimes', 'integer', 'between:6,60'],
            'country' => ['nullable', 'string', 'max:100'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'activity_mode' => ['required', 'string', 'max:30'],
        ];
    }
}
