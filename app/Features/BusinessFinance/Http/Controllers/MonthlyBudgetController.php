<?php

namespace App\Features\BusinessFinance\Http\Controllers;

use App\Features\BusinessFinance\Http\Resources\MonthlyBudgetResource;
use App\Features\BusinessFinance\Models\Company;
use App\Features\BusinessFinance\Models\MonthlyBudget;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class MonthlyBudgetController extends Controller
{
    public function index(Company $company): AnonymousResourceCollection
    {
        $company = $this->ownedCompany($company);

        return MonthlyBudgetResource::collection(
            $company->budgets()->orderByDesc('year')->orderByDesc('month')->paginate(15),
        );
    }

    public function store(Request $request, Company $company): JsonResponse
    {
        $company = $this->ownedCompany($company);
        $validated = $request->validate([
            'year' => ['required', 'integer', 'between:2000,32767'],
            'month' => ['required', 'integer', 'between:1,12'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);
        $this->ensurePeriodAvailable($company, $validated['year'], $validated['month']);
        $budget = $company->budgets()->create($validated);

        return (new MonthlyBudgetResource($budget))->response()->setStatusCode(201);
    }

    public function show(Company $company, MonthlyBudget $monthlyBudget): MonthlyBudgetResource
    {
        $company = $this->ownedCompany($company);

        return new MonthlyBudgetResource($company->budgets()->findOrFail($monthlyBudget->id));
    }

    public function update(Request $request, Company $company, MonthlyBudget $monthlyBudget): MonthlyBudgetResource
    {
        $company = $this->ownedCompany($company);
        $budget = $company->budgets()->findOrFail($monthlyBudget->id);
        $validated = $request->validate([
            'year' => ['sometimes', 'required', 'integer', 'between:2000,32767'],
            'month' => ['sometimes', 'required', 'integer', 'between:1,12'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0'],
        ]);
        $this->ensurePeriodAvailable(
            $company,
            (int) ($validated['year'] ?? $budget->year),
            (int) ($validated['month'] ?? $budget->month),
            $budget,
        );
        $budget->update($validated);

        return new MonthlyBudgetResource($budget->refresh());
    }

    public function destroy(Company $company, MonthlyBudget $monthlyBudget): Response
    {
        $company = $this->ownedCompany($company);
        $company->budgets()->findOrFail($monthlyBudget->id)->delete();

        return response()->noContent();
    }

    private function ownedCompany(Company $company): Company
    {
        return Auth::user()->companies()->findOrFail($company->id);
    }

    private function ensurePeriodAvailable(Company $company, int $year, int $month, ?MonthlyBudget $budget = null): void
    {
        $query = $company->budgets()
            ->where('year', $year)
            ->where('month', $month);

        if ($budget !== null) {
            $query->where('id', '!=', $budget->id);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'month' => ['Un budget existe déjà pour cette entreprise et cette période.'],
            ]);
        }
    }
}
