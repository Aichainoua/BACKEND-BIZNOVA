<?php

namespace App\Features\ProjectAnalysis\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveProjectHypothesesRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $data = $this->all();

        if (isset($data['activity_lines']) && is_array($data['activity_lines'])) {
            $data['activity_lines'] = array_map(function ($line) {
                $line['type'] = match (strtolower($line['type'] ?? '')) {
                    'commerce_global' => 'COMMERCE_GLOBAL',
                    'family', 'famille' => 'FAMILLE',
                    'service', 'prestation' => 'PRESTATION',
                    'subscription', 'abonnement' => 'ABONNEMENT',
                    default => strtoupper($line['type'] ?? ''),
                };

                return $line;
            }, $data['activity_lines']);
        }

        if (isset($data['expenses']) && is_array($data['expenses'])) {
            $data['expenses'] = array_map(function ($expense) {
                $expense['nature'] = strtoupper($expense['nature'] ?? '');

                return $expense;
            }, $data['expenses']);
        }

        $this->replace($data);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'funding' => ['required', 'array'],

            'funding.initial_investment_total' => [
                'required',
                'numeric',
                'min:0',
            ],
            'funding.personal_contribution' => [
                'sometimes',
                'numeric',
                'min:0',
            ],
            'funding.non_loan_funding' => [
                'sometimes',
                'numeric',
                'min:0',
            ],
            'funding.loan_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'funding.annual_interest_rate' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'funding.loan_duration_months' => [
                'nullable',
                'integer',
                'min:1',
                'max:600',
            ],
            'funding.repayment_frequency' => [
                'nullable',
                'string',
                'max:30',
            ],
            'funding.deferment_months' => [
                'nullable',
                'integer',
                'min:0',
                'max:600',
            ],

            'activity_lines' => ['required', 'array', 'min:1'],
            'activity_lines.*.type' => [
                'required',
                Rule::in([
                    'COMMERCE_GLOBAL',
                    'FAMILLE',
                    'PRESTATION',
                    'ABONNEMENT',
                ]),
            ],
            'activity_lines.*.designation' => [
                'required',
                'string',
                'max:150',
            ],
            'activity_lines.*.monthly_revenue' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'activity_lines.*.monthly_cost_of_goods_sold' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'activity_lines.*.unit_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'activity_lines.*.monthly_volume' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'activity_lines.*.unit_direct_cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'activity_lines.*.monthly_subscription_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'activity_lines.*.new_subscribers_per_month' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'activity_lines.*.active_subscribers' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'expenses' => ['present', 'array'],
            'expenses.*.nature' => [
                'required',
                Rule::in(['FIXE', 'VARIABLE']),
            ],
            'expenses.*.label' => [
                'required',
                'string',
                'max:150',
            ],
            'expenses.*.monthly_amount' => [
                'required',
                'numeric',
                'min:0',
            ],
            'expenses.*.start_month' => [
                'required',
                'integer',
                'min:1',
                'max:60',
            ],
            'expenses.*.end_month' => [
                'nullable',
                'integer',
                'min:1',
                'max:60',
                'gte:expenses.*.start_month',
            ],
        ];
    }
}
