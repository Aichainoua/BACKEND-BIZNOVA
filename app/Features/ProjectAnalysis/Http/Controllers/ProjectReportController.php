<?php

namespace App\Features\ProjectAnalysis\Http\Controllers;

use App\Features\ProjectAnalysis\Models\Project;
use App\Features\ProjectAnalysis\Services\ProjectReportService;
use App\Http\Controllers\Controller;
use Illuminate\Filesystem\FilesystemAdapter;
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
            ->findOrFail($project->id);

        $reports = $project->reports()
            ->orderByDesc('generated_at')
            ->get()
            ->map(fn ($report) => [
                'id' => $report->id,
                'file_name' => $report->file_name,
                'generated_at' => $report->generated_at,
                'period_start' => $report->period_start?->toDateString(),
                'period_end' => $report->period_end?->toDateString(),
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
            ->findOrFail($project->id);

        $report = $reportService->generate($project);

        return response()->json([
            'message' => 'Rapport prévisionnel généré avec succès.',
            'data' => [
                'id' => $report->id,
                'file_name' => $report->file_name,
                'generated_at' => $report->generated_at,
                'period_start' => $report->period_start?->toDateString(),
                'period_end' => $report->period_end?->toDateString(),
            ],
        ], 201);
    }

    public function download(
        Project $project,
        int $report
    ): StreamedResponse {
        $project = Auth::user()
            ->projects()
            ->findOrFail($project->id);

        $report = $project->reports()
            ->findOrFail($report);

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        abort_unless($disk->exists($report->file_path), 404, 'Le fichier PDF est introuvable.');

        return $disk->download(
            $report->file_path,
            $report->file_name,
            ['Content-Type' => 'application/pdf']
        );
    }
}
