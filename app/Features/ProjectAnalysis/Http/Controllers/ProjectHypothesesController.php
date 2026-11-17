<?php

namespace App\Features\ProjectAnalysis\Http\Controllers;

use App\Features\ProjectAnalysis\Http\Requests\SaveProjectHypothesesRequest;
use App\Features\ProjectAnalysis\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectHypothesesController extends Controller
{
    public function show(Project $project): JsonResponse
    {
        $project = Auth::user()
            ->projects()
            ->with(['funding', 'activityLines', 'expenses'])
            ->findOrFail($project->id);

        return response()->json([
            'data' => $this->formatHypotheses($project),
        ]);
    }

    public function update(
        SaveProjectHypothesesRequest $request,
        Project $project
    ): JsonResponse {
        $project = Auth::user()
            ->projects()
            ->findOrFail($project->id);

        $data = $request->validated();

        foreach ($data['expenses'] as $expense) {
            if (
                isset($expense['end_month'])
                && $expense['end_month'] > $project->horizon_months
            ) {
                throw ValidationException::withMessages([
                    'expenses' => [
                        'Le mois de fin d’une charge ne peut pas dépasser l’horizon du projet.',
                    ],
                ]);
            }
        }

        DB::transaction(function () use ($project, $data): void {
            $funding = $data['funding'];

            $project->funding()->updateOrCreate([], [
                'initial_investment_total' => $funding['initial_investment_total'],

                'personal_contribution' => $funding['personal_contribution'] ?? 0,

                'non_loan_funding' => $funding['non_loan_funding'] ?? 0,

                'loan_amount' => $funding['loan_amount'] ?? null,

                'annual_interest_rate' => $funding['annual_interest_rate'] ?? 0,

                'loan_duration_months' => $funding['loan_duration_months'] ?? null,

                'repayment_frequency' => $funding['repayment_frequency'] ?? null,

                'deferment_months' => $funding['deferment_months'] ?? 0,
            ]);

            $project->activityLines()->delete();

            $activityLines = array_map(
                fn (array $line): array => [
                    'line_type' => $line['type'],
                    'designation' => $line['designation'],
                    'monthly_revenue' => $line['monthly_revenue'] ?? null,
                    'monthly_cost_of_goods_sold' => $line['monthly_cost_of_goods_sold'] ?? null,
                    'unit_price' => $line['unit_price'] ?? null,
                    'monthly_volume' => $line['monthly_volume'] ?? null,
                    'unit_direct_cost' => $line['unit_direct_cost'] ?? null,
                    'monthly_subscription_price' => $line['monthly_subscription_price'] ?? null,
                    'new_subscribers_per_month' => $line['new_subscribers_per_month'] ?? null,
                    'active_subscribers' => $line['active_subscribers'] ?? null,
                ],
                $data['activity_lines']
            );

            $project->activityLines()->createMany($activityLines);

            $project->expenses()->delete();

            $expenses = array_map(
                fn (array $expense): array => [
                    'nature' => $expense['nature'],
                    'label' => $expense['label'],
                    'monthly_amount' => $expense['monthly_amount'],
                    'start_month' => $expense['start_month'],
                    'end_month' => $expense['end_month'] ?? null,
                ],
                $data['expenses']
            );

            if (! empty($expenses)) {
                $project->expenses()->createMany($expenses);
            }
        });

        $project->load(['funding', 'activityLines', 'expenses']);

        return response()->json([
            'message' => 'Les hypothèses du projet ont été enregistrées.',
            'data' => $this->formatHypotheses($project),
        ]);
    }

    private function formatHypotheses(Project $project): array
    {
        $funding = $project->funding;

        return [
            'funding' => $funding ? [
                'initial_investment_total' => $funding->initial_investment_total,
                'personal_contribution' => $funding->personal_contribution,
                'non_loan_funding' => $funding->non_loan_funding,
                'loan_amount' => $funding->loan_amount,
                'annual_interest_rate' => $funding->annual_interest_rate,
                'loan_duration_months' => $funding->loan_duration_months,
                'repayment_frequency' => $funding->repayment_frequency,
                'deferment_months' => $funding->deferment_months,
            ] : null,

            'activity_lines' => $project->activityLines->map(fn ($line) => [
                'id' => $line->id,
                'type' => $line->line_type,
                'designation' => $line->designation,
                'monthly_revenue' => $line->monthly_revenue,
                'monthly_cost_of_goods_sold' => $line->monthly_cost_of_goods_sold,
                'unit_price' => $line->unit_price,
                'monthly_volume' => $line->monthly_volume,
                'unit_direct_cost' => $line->unit_direct_cost,
                'monthly_subscription_price' => $line->monthly_subscription_price,
                'new_subscribers_per_month' => $line->new_subscribers_per_month,
                'active_subscribers' => $line->active_subscribers,
            ])->values(),

            'expenses' => $project->expenses->map(fn ($expense) => [
                'id' => $expense->id,
                'nature' => $expense->nature,
                'label' => $expense->label,
                'monthly_amount' => $expense->monthly_amount,
                'start_month' => $expense->start_month,
                'end_month' => $expense->end_month,
            ])->values(),
        ];
    }
}
