<?php

namespace App\Features\BusinessFinance\Http\Controllers;

use App\Features\BusinessFinance\Http\Resources\TagResource;
use App\Features\BusinessFinance\Models\Company;
use App\Features\BusinessFinance\Models\Tag;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    public function index(Company $company): AnonymousResourceCollection
    {
        $company = $this->ownedCompany($company);

        return TagResource::collection($company->tags()->orderBy('label')->paginate(15));
    }

    public function store(Request $request, Company $company): JsonResponse
    {
        $company = $this->ownedCompany($company);
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:80', Rule::unique('tags', 'label')->where('company_id', $company->id)],
        ]);
        $tag = $company->tags()->create($validated);

        return (new TagResource($tag))->response()->setStatusCode(201);
    }

    public function show(Company $company, Tag $tag): TagResource
    {
        $company = $this->ownedCompany($company);

        return new TagResource($company->tags()->findOrFail($tag->id));
    }

    public function update(Request $request, Company $company, Tag $tag): TagResource
    {
        $company = $this->ownedCompany($company);
        $tag = $company->tags()->findOrFail($tag->id);
        $validated = $request->validate([
            'label' => [
                'sometimes',
                'required',
                'string',
                'max:80',
                Rule::unique('tags', 'label')->where('company_id', $company->id)->ignore($tag),
            ],
        ]);
        $tag->update($validated);

        return new TagResource($tag->refresh());
    }

    public function destroy(Company $company, Tag $tag): Response
    {
        $company = $this->ownedCompany($company);
        $company->tags()->findOrFail($tag->id)->delete();

        return response()->noContent();
    }

    private function ownedCompany(Company $company): Company
    {
        return Auth::user()->companies()->findOrFail($company->id);
    }
}
