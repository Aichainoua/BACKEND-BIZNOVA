<?php

namespace App\Features\BusinessFinance\Http\Controllers;

use App\Features\BusinessFinance\Http\Requests\StoreCompanyRequest;
use App\Features\BusinessFinance\Http\Requests\UpdateCompanyRequest;
use App\Features\BusinessFinance\Http\Resources\CompanyResource;
use App\Features\BusinessFinance\Models\Company;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $user = Auth::user();

        return CompanyResource::collection(
            $user->companies()->latest('id')->paginate(15),
        );
    }

    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $user = Auth::user();
        $company = $user->companies()->create($request->validated());

        return (new CompanyResource($company))->response()->setStatusCode(201);
    }

    public function show(Company $company): CompanyResource
    {
        $user = Auth::user();
        $company = $user->companies()->findOrFail($company->id);

        return new CompanyResource($company);
    }

    public function update(UpdateCompanyRequest $request, Company $company): CompanyResource
    {
        $user = Auth::user();
        $company = $user->companies()->findOrFail($company->id);
        $company->update($request->validated());

        return new CompanyResource($company->refresh());
    }

    public function destroy(Company $company): Response
    {
        $user = Auth::user();
        $user->companies()->findOrFail($company->id)->delete();

        return response()->noContent();
    }
}
