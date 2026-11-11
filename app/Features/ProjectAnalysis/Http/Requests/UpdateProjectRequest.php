<?php

namespace App\Features\ProjectAnalysis\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
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
            'launch_date' => ['sometimes', 'required', 'date'],
            'horizon_months' => ['sometimes', 'integer', 'between:6,60'],
            'country' => ['sometimes', 'nullable', 'string', 'max:100'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'activity_mode' => ['sometimes', 'required', 'string', 'max:30'],
        ];
    }
}
