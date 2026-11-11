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
        return response()->json([
            'data' => Sector::query()->orderBy('label')->get(['id', 'code', 'label']),
        ]);
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
                fn (Builder $query): Builder => $query->where(function (Builder $query) use ($validated): void {
                    $query->where('sector_id', $validated['sector_id'])
                        ->orWhereNull('sector_id');
                }),
            )
            ->when(
                array_key_exists('type', $validated),
                fn (Builder $query): Builder => $query->where('type', $validated['type']),
            )
            ->orderBy('label')
            ->get(['id', 'sector_id', 'type', 'label', 'is_active']);

        return response()->json(['data' => $categories]);
    }
}
