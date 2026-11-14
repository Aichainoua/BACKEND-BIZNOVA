<?php

namespace App\Features\BusinessFinance\Http\Controllers;

use App\Features\BusinessFinance\Actions\GetCompanyDashboardSummary;
use App\Features\BusinessFinance\Http\Requests\CompanyDashboardRequest;
use App\Features\BusinessFinance\Models\Company;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CompanyDashboardController extends Controller
{
    public function __invoke(
        CompanyDashboardRequest $request,
        Company $company,
        GetCompanyDashboardSummary $dashboardSummary,
    ): JsonResponse {
        $company = $request->user()->companies()->findOrFail($company->id);
        $year = (int) ($request->validated()['year'] ?? now()->year);

        return response()->json([
            'data' => $dashboardSummary->forYear($company, $year),
        ]);
    }
}
