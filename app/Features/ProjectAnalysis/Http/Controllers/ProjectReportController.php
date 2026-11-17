<?php

namespace App\Features\ProjectAnalysis\Http\Controllers;

use App\Features\ProjectAnalysis\Models\Project;
use App\Features\ProjectAnalysis\Services\ProjectReportService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectReportController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        $project = Auth::user()
            ->projects()
            ->findOrFail($project->id_projet);

        $reports = $project->reports()
            ->orderByDesc('date_generation')
            ->get()
            ->map(fn ($report) => [
                'id' => $report->id_rapport_projet,
                'file_name' => $report->nom_fichier,
                'generated_at' => $report->date_generation,
                'period_start' => $report->periode_debut,
                'period_end' => $report->periode_fin,
            ]);

        return response()->json([
            'data' => $reports,
        ]);
    }

    public function store(
        Project $project,
        ProjectReportService $reportService
    ): JsonResponse {
        $project = Auth::user()
            ->projects()
            ->findOrFail($project->id_projet);

        $report = $reportService->generate($project);

        return response()->json([
            'message' => 'Rapport prévisionnel généré avec succès.',
            'data' => [
                'id' => $report->id_rapport_projet,
                'file_name' => $report->nom_fichier,
                'generated_at' => $report->date_generation,
                'period_start' => $report->periode_debut,
                'period_end' => $report->periode_fin,
            ],
        ], 201);
    }

    public function download(
        Project $project,
        int $report
    ): StreamedResponse {
        $project = Auth::user()
            ->projects()
            ->findOrFail($project->id_projet);

        $report = $project->reports()
            ->findOrFail($report);

        abort_unless(
            Storage::disk('local')->exists($report->chemin_fichier),
            404,
            'Le fichier PDF est introuvable.'
        );

        return Storage::disk('local')->download(
            $report->chemin_fichier,
            $report->nom_fichier,
            ['Content-Type' => 'application/pdf']
        );
    }
}
