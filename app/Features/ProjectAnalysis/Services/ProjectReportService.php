<?php

namespace App\Features\ProjectAnalysis\Services;

use App\Features\ProjectAnalysis\Models\Project;
use App\Features\ProjectAnalysis\Models\ProjectReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ProjectReportService
{
    public function __construct(
        private FinancialCalculationService $calculationService
    ) {}

    public function generate(Project $project): ProjectReport
    {
        $project->loadMissing([
            'funding',
            'activityLines',
            'expenses',
        ]);

        $analysis = $this->calculationService->calculate($project);
        $launchDate = $project->launch_date->copy()->startOfMonth();
        $periodStart = $launchDate->toDateString();
        $periodEnd = $launchDate
            ->copy()
            ->addMonths((int) $project->horizon_months)
            ->subDay()
            ->toDateString();

        $filename = 'project-report-'
            .$project->id
            .'-'
            .Str::uuid()
            .'.pdf';
        $filePath = 'project-reports/'.$project->id.'/'.$filename;

        $pdf = Pdf::loadView('project-analysis.report', [
            'project' => $project,
            'analysis' => $analysis,
            'generatedAt' => now(),
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
        ])->setPaper('a4', 'portrait');

        $saved = Storage::disk('local')->put($filePath, $pdf->output());

        if (! $saved) {
            throw new RuntimeException('Impossible d’enregistrer le rapport PDF.');
        }

        try {
            return $project->reports()->create([
                'generated_at' => now(),
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'currency' => $project->currency,
                'file_name' => $filename,
                'file_path' => $filePath,
                'data_snapshot' => $analysis,
            ]);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($filePath);
            throw $exception;
        }
    }
}
