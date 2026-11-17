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
            ->orderBy('label')
            ->get(['id', 'code', 'label'])
            ->map(fn ($sector) => [
                'id' => $sector->id,
                'code' => strtolower($sector->code),
                'label' => $sector->label,
            ]);

        return response()->json(['data' => $sectors]);
    }

    public function categories(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sector_id' => ['sometimes', 'integer', 'exists:sectors,id'],
            'type' => ['sometimes', 'string', 'in:income,expense'],
        ]);

        $categories = Category::query()
            ->where('is_active', true)
            ->when(
                array_key_exists('sector_id', $validated),
                fn (Builder $query) => $query->where(function (Builder $query) use ($validated) {
                    $query->where('sector_id', $validated['sector_id'])
                        ->orWhereNull('sector_id');
                }),
            )
            ->when(
                array_key_exists('type', $validated),
                fn (Builder $query) => $query->where(
                    'type',
                    $validated['type']
                ),
            )
            ->orderBy('label')
            ->get()
            ->map(fn ($category) => [
                'id' => $category->id,
                'sector_id' => $category->sector_id,
                'type' => $category->type,
                'label' => $category->label,
                'is_active' => $category->is_active,
            ]);

        return response()->json(['data' => $categories]);
    }
}
