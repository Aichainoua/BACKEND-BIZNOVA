<?php

namespace App\Features\BusinessFinance\Http\Controllers;

use App\Features\BusinessFinance\Http\Requests\StoreFinancialOperationRequest;
use App\Features\BusinessFinance\Http\Requests\UpdateFinancialOperationRequest;
use App\Features\BusinessFinance\Http\Resources\FinancialOperationResource;
use App\Features\BusinessFinance\Models\Company;
use App\Features\BusinessFinance\Models\FinancialOperation;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class FinancialOperationController extends Controller
{
    public function index(Company $company): AnonymousResourceCollection
    {
        $company = $this->ownedCompany($company);

        return FinancialOperationResource::collection(
            $company->operations()->latest('operation_date')->latest('id')->paginate(15),
        );
    }

    public function store(StoreFinancialOperationRequest $request, Company $company): JsonResponse
    {
        $company = $this->ownedCompany($company);
        $operation = $company->operations()->create($request->validated());

        return (new FinancialOperationResource($operation))->response()->setStatusCode(201);
    }

    public function show(Company $company, FinancialOperation $operation): FinancialOperationResource
    {
        $company = $this->ownedCompany($company);
        $operation = $company->operations()->findOrFail($operation->id);

        return new FinancialOperationResource($operation);
    }

    public function update(
        UpdateFinancialOperationRequest $request,
        Company $company,
        FinancialOperation $operation,
    ): FinancialOperationResource {
        $company = $this->ownedCompany($company);
        $operation = $company->operations()->findOrFail($operation->id);
        $operation->update($request->validated());

        return new FinancialOperationResource($operation->refresh());
    }

    public function destroy(Company $company, FinancialOperation $operation): Response
    {
        $company = $this->ownedCompany($company);
        $company->operations()->findOrFail($operation->id)->delete();

        return response()->noContent();
    }

    private function ownedCompany(Company $company): Company
    {
        return Auth::user()->companies()->findOrFail($company->id);
    }
}
