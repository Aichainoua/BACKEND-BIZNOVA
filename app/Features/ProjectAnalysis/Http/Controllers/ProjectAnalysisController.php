<?php

namespace App\Features\ProjectAnalysis\Http\Controllers;

use App\Features\ProjectAnalysis\Models\Project;
use App\Features\ProjectAnalysis\Services\FinancialCalculationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProjectAnalysisController extends Controller
{
    public function show(
        Project $project,
        FinancialCalculationService $calculationService
    ): JsonResponse {
        $project = Auth::user()
            ->projects()
            ->findOrFail($project->id_projet);

        $results = $calculationService->calculate($project);

        return response()->json([
            'message' => 'Analyse financière calculée avec succès.',
            'data' => $results,
        ]);
    }
}
