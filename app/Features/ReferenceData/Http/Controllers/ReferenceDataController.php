<?php

namespace App\Features\ReferenceData\Http\Controllers;

use App\Features\ReferenceData\Models\Category;
use App\Features\ReferenceData\Models\Sector;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferenceDataController extends Controller
{
    public function sectors(): JsonResponse
    {
        $sectors = Sector::query()
            ->orderBy('libelle')
            ->get(['id_secteur', 'code', 'libelle'])
            ->map(fn (Sector $sector) => [
                'id' => $sector->id_secteur,
                'code' => strtolower($sector->code),
                'label' => $sector->libelle,
            ]);

        return response()->json(['data' => $sectors]);
    }

    public function categories(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sector_id' => ['sometimes', 'integer', 'exists:secteur,id_secteur'],
            'type' => ['sometimes', 'string', 'in:income,expense'],
        ]);

        $typeMap = [
            'income' => 'RECETTE',
            'expense' => 'DEPENSE',
        ];

        $categories = Category::query()
            ->where('actif', true)
            ->when(
                array_key_exists('sector_id', $validated),
                fn (Builder $query) => $query->where(function (Builder $query) use ($validated) {
                    $query->where('id_secteur', $validated['sector_id'])
                        ->orWhereNull('id_secteur');
                }),
            )
            ->when(
                array_key_exists('type', $validated),
                fn (Builder $query) => $query->where(
                    'type_operation',
                    $typeMap[$validated['type']]
                ),
            )
            ->orderBy('libelle')
            ->get()
            ->map(fn (Category $category) => [
                'id' => $category->id_categorie,
                'sector_id' => $category->id_secteur,
                'type' => $category->type_operation === 'RECETTE' ? 'income' : 'expense',
                'label' => $category->libelle,
                'is_active' => $category->actif,
            ]);

        return response()->json(['data' => $categories]);
    }
}
