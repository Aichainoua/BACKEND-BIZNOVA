<?php

namespace App\Features\ProjectAnalysis\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $mode = $this->input('activity_mode');

        if ($mode !== null) {
            $this->merge([
                'activity_mode' => match (strtolower($mode)) {
                    'global' => 'GLOBAL',
                    'detailed', 'detaille' => 'DETAILLE',
                    default => strtoupper($mode),
                },
            ]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sector_id' => ['sometimes', 'required', 'integer', 'exists:secteur,id_secteur'],
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'launch_date' => ['sometimes', 'required', 'date'],
            'horizon_months' => ['sometimes', 'integer', 'between:6,60'],
            'country' => ['sometimes', 'nullable', 'string', 'max:100'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'activity_mode' => ['sometimes', 'required', Rule::in(['GLOBAL', 'DETAILLE'])],
        ];
    }
}
