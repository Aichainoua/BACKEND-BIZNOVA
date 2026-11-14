<?php

namespace App\Features\BusinessFinance\Actions;

use App\Features\BusinessFinance\Models\Company;
use App\Features\BusinessFinance\Models\FinancialOperation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class GetCompanyDashboardSummary
{
    public function forYear(Company $company, int $year): array
    {
        $yearStart = Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd = Carbon::create($year, 12, 31)->endOfDay();
        $totals = $this->operationTotals($company, $yearStart, $yearEnd);

        $monthlyTrends = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthStart = Carbon::create($year, $month, 1)->startOfDay();
            $monthEnd = $monthStart->copy()->endOfMonth()->endOfDay();
            $monthlyTotals = $this->operationTotals($company, $monthStart, $monthEnd);

            $monthlyTrends[] = [
                'month' => $month,
                'income' => $this->formatAmount($monthlyTotals->income),
                'expenses' => $this->formatAmount($monthlyTotals->expenses),
                'net' => $this->formatAmount($monthlyTotals->net),
            ];
        }

        $budgets = $company->budgets()
            ->where('year', $year)
            ->get(['month', 'amount'])
            ->keyBy('month');

        return [
            'year' => $year,
            'totals' => [
                'income' => $this->formatAmount($totals->income),
                'expenses' => $this->formatAmount($totals->expenses),
                'net' => $this->formatAmount($totals->net),
            ],
            'monthly_trends' => $monthlyTrends,
            'category_breakdown' => $this->categoryBreakdown($company, $yearStart, $yearEnd),
            'budget_comparison' => collect($monthlyTrends)
                ->map(fn (array $month): array => [
                    'month' => $month['month'],
                    'budgeted' => $this->formatAmount($budgets->get($month['month'])?->amount ?? 0),
                    'expenses' => $month['expenses'],
                ])
                ->all(),
            'tag_breakdown' => $this->tagBreakdown($company, $yearStart, $yearEnd),
        ];
    }

    private function operationTotals(Company $company, Carbon $start, Carbon $end): FinancialOperation
    {
        return $this->operationsForPeriod($company, $start, $end)
            ->join('categories', 'categories.id', '=', 'financial_operations.category_id')
            ->select([])
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN categories.type = ? THEN financial_operations.amount ELSE 0 END), 0) as income',
                ['income'],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN categories.type = ? THEN financial_operations.amount ELSE 0 END), 0) as expenses',
                ['expense'],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN categories.type = ? THEN financial_operations.amount ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN categories.type = ? THEN financial_operations.amount ELSE 0 END), 0) as net',
                ['income', 'expense'],
            )
            ->firstOrFail();
    }

    private function categoryBreakdown(Company $company, Carbon $start, Carbon $end): array
    {
        return $this->operationsForPeriod($company, $start, $end)
            ->join('categories', 'categories.id', '=', 'financial_operations.category_id')
            ->select([
                'categories.id as category_id',
                'categories.label as category',
                'categories.type',
            ])
            ->selectRaw('SUM(financial_operations.amount) as total')
            ->groupBy('categories.id', 'categories.label', 'categories.type')
            ->orderBy('categories.type')
            ->orderBy('categories.label')
            ->get()
            ->map(fn (FinancialOperation $operation): array => [
                'category_id' => $operation->category_id,
                'category' => $operation->category,
                'type' => $operation->type,
                'total' => $this->formatAmount($operation->total),
            ])
            ->all();
    }

    private function tagBreakdown(Company $company, Carbon $start, Carbon $end): array
    {
        return $this->operationsForPeriod($company, $start, $end)
            ->join('operation_tag', 'operation_tag.operation_id', '=', 'financial_operations.id')
            ->join('tags', function ($join): void {
                $join->on('tags.id', '=', 'operation_tag.tag_id')
                    ->on('tags.company_id', '=', 'operation_tag.company_id');
            })
            ->join('categories', 'categories.id', '=', 'financial_operations.category_id')
            ->where('operation_tag.company_id', $company->id)
            ->select([
                'tags.id as tag_id',
                'tags.label',
            ])
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN categories.type = ? THEN financial_operations.amount ELSE 0 END), 0) as income',
                ['income'],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN categories.type = ? THEN financial_operations.amount ELSE 0 END), 0) as expenses',
                ['expense'],
            )
            ->groupBy('tags.id', 'tags.label')
            ->orderBy('tags.label')
            ->get()
            ->map(fn (FinancialOperation $operation): array => [
                'tag_id' => $operation->tag_id,
                'label' => $operation->label,
                'income' => $this->formatAmount($operation->income),
                'expenses' => $this->formatAmount($operation->expenses),
            ])
            ->all();
    }

    private function operationsForPeriod(Company $company, Carbon $start, Carbon $end): Builder
    {
        return FinancialOperation::query()
            ->where('financial_operations.company_id', $company->id)
            ->whereBetween('financial_operations.operation_date', [
                $start->toDateString(),
                $end->toDateString(),
            ]);
    }

    private function formatAmount(string|int|float $amount): string
    {
        $value = (string) $amount;

        if (preg_match('/^-?\d+(?:\.\d{1,2})?$/D', $value) === 1) {
            [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');

            return $whole.'.'.str_pad($fraction, 2, '0');
        }

        return number_format((float) $amount, 2, '.', '');
    }
}
