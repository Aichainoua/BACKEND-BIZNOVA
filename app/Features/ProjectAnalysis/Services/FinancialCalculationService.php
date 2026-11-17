<?php

namespace App\Features\ProjectAnalysis\Services;

use App\Features\ProjectAnalysis\Models\Project;

class FinancialCalculationService
{
    public function calculate(Project $project): array
    {
        $project->loadMissing([
            'funding',
            'activityLines',
            'expenses',
        ]);

        $horizon = (int) $project->horizon_months;
        $funding = $project->funding;

        $initialInvestment = (float) ($funding?->initial_investment_total ?? 0);

        $monthlyForecast = [];

        $totalRevenue = 0;
        $totalCogs = 0;
        $totalFixedExpenses = 0;
        $totalVariableExpenses = 0;
        $totalOperatingResult = 0;

        $cumulativeCashFlow = -$initialInvestment;
        $paybackMonth = null;

        for ($month = 1; $month <= $horizon; $month++) {
            $revenue = 0;
            $cogs = 0;
            $fixedExpenses = 0;
            $variableExpenses = 0;

            foreach ($project->activityLines as $line) {
                $type = $line->line_type;

                if ($type === 'COMMERCE_GLOBAL') {
                    $revenue += (float) $line->monthly_revenue;
                    $cogs += (float) $line->monthly_cost_of_goods_sold;
                }

                if (in_array($type, ['FAMILLE', 'PRESTATION'], true)) {
                    $quantity = (float) $line->monthly_volume;
                    $unitPrice = (float) $line->unit_price;
                    $unitCost = (float) $line->unit_direct_cost;

                    $revenue += $unitPrice * $quantity;
                    $cogs += $unitCost * $quantity;
                }

                if ($type === 'ABONNEMENT') {
                    $baseSubscribers = (float) $line->active_subscribers;
                    $newSubscribers = (float) $line->new_subscribers_per_month;

                    $activeSubscribers = $baseSubscribers
                        + ($newSubscribers * ($month - 1));

                    $revenue += (float) $line->monthly_subscription_price
                        * $activeSubscribers;

                    $cogs += (float) $line->unit_direct_cost
                        * $activeSubscribers;
                }
            }

            foreach ($project->expenses as $expense) {
                $startMonth = (int) ($expense->start_month ?? 1);
                $endMonth = (int) ($expense->end_month ?? $horizon);

                if ($month < $startMonth || $month > $endMonth) {
                    continue;
                }

                $amount = (float) $expense->monthly_amount;

                if ($expense->nature === 'FIXE') {
                    $fixedExpenses += $amount;
                } elseif ($expense->nature === 'VARIABLE') {
                    $variableExpenses += $amount;
                }
            }

            $totalExpenses = $cogs + $fixedExpenses + $variableExpenses;
            $operatingResult = $revenue - $totalExpenses;

            $operatingMargin = $revenue > 0
                ? ($operatingResult / $revenue) * 100
                : null;

            $cumulativeCashFlow += $operatingResult;

            if ($paybackMonth === null && $cumulativeCashFlow >= 0) {
                $paybackMonth = $month;
            }

            $monthlyForecast[] = [
                'month' => $month,
                'revenue' => round($revenue, 2),
                'cost_of_goods_sold' => round($cogs, 2),
                'fixed_expenses' => round($fixedExpenses, 2),
                'variable_expenses' => round($variableExpenses, 2),
                'total_expenses' => round($totalExpenses, 2),
                'operating_result' => round($operatingResult, 2),
                'operating_margin_percentage' => $operatingMargin !== null
                    ? round($operatingMargin, 2)
                    : null,
                'cumulative_cash_flow' => round($cumulativeCashFlow, 2),
            ];

            $totalRevenue += $revenue;
            $totalCogs += $cogs;
            $totalFixedExpenses += $fixedExpenses;
            $totalVariableExpenses += $variableExpenses;
            $totalOperatingResult += $operatingResult;
        }

        $totalExpenses = $totalCogs
            + $totalFixedExpenses
            + $totalVariableExpenses;

        $operatingMargin = $totalRevenue > 0
            ? ($totalOperatingResult / $totalRevenue) * 100
            : null;

        $roi = $initialInvestment > 0
            ? (($totalOperatingResult - $initialInvestment) / $initialInvestment) * 100
            : null;

        $averageMonthlyFixedExpenses = $horizon > 0
            ? $totalFixedExpenses / $horizon
            : 0;

        $contributionMarginRate = $totalRevenue > 0
            ? ($totalRevenue - $totalCogs - $totalVariableExpenses) / $totalRevenue
            : 0;

        $breakEvenRevenue = $contributionMarginRate > 0
            ? $averageMonthlyFixedExpenses / $contributionMarginRate
            : null;

        $warnings = [];

        if ($initialInvestment <= 0) {
            $warnings[] = 'Aucun investissement initial positif n’a été renseigné.';
        }

        if ($totalRevenue <= 0) {
            $warnings[] = 'Aucun chiffre d’affaires prévisionnel positif.';
        }

        if ($contributionMarginRate <= 0) {
            $warnings[] = 'La marge sur coûts variables est nulle ou négative.';
        }

        if ($paybackMonth === null) {
            $warnings[] = 'L’investissement initial n’est pas récupéré sur la période prévisionnelle.';
        }

        return [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'currency' => trim($project->currency),
                'horizon_months' => $horizon,
                'launch_date' => $project->launch_date?->format('Y-m-d'),
            ],

            'summary' => [
                'initial_investment' => round($initialInvestment, 2),
                'total_revenue' => round($totalRevenue, 2),
                'total_cost_of_goods_sold' => round($totalCogs, 2),
                'total_fixed_expenses' => round($totalFixedExpenses, 2),
                'total_variable_expenses' => round($totalVariableExpenses, 2),
                'total_expenses' => round($totalExpenses, 2),
                'total_operating_result' => round($totalOperatingResult, 2),
                'operating_margin_percentage' => $operatingMargin !== null
                    ? round($operatingMargin, 2)
                    : null,
                'roi_percentage' => $roi !== null ? round($roi, 2) : null,
                'break_even_monthly_revenue' => $breakEvenRevenue !== null
                    ? round($breakEvenRevenue, 2)
                    : null,
                'payback_month' => $paybackMonth,
                'final_cumulative_cash_flow' => round($cumulativeCashFlow, 2),
            ],

            'monthly_forecast' => $monthlyForecast,

            'warnings' => $warnings,

            'calculation_assumptions' => [
                'revenues_and_expenses_are_projected_from_user_inputs' => true,
                'subscription_growth_assumes_no_churn' => true,
                'loan_repayments_included' => false,
                'taxes_included' => false,
                'depreciation_included' => false,
                'working_capital_included' => false,
            ],
        ];
    }
}
