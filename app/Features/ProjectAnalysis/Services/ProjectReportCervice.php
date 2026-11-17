<?php

namespace App\Features\ProjectAnalysis\Services;

use App\Features\ProjectAnalysis\Models\Project;
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

    public function generate(Project $project)
    {
        $project->loadMissing([
            'funding',
            'activityLines',
            'expenses',
        ]);

        $analysis = $this->calculationService->calculate($project);

        $launchDate = $project->date_lancement->copy()->startOfMonth();
        $periodStart = $launchDate->toDateString();

        $periodEnd = $launchDate
            ->copy()
            ->addMonths((int) $project->horizon_mois)
            ->subDay()
            ->toDateString();

        $filename = 'rapport-projet-'
            . $project->id_projet
            . '-'
            . Str::uuid()
            . '.pdf';

        $filePath = 'project-reports/'
            . $project->id_projet
            . '/'
            . $filename;

        $pdf = Pdf::loadView('project-analysis.report', [
            'project' => $project,
            'analysis' => $analysis,
            'generatedAt' => now(),
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
        ])->setPaper('a4', 'portrait');

        $saved = Storage::disk('local')->put(
            $filePath,
            $pdf->output()
        );

        if (!$saved) {
            throw new RuntimeException('Impossible d’enregistrer le rapport PDF.');
        }

        try {
            return $project->reports()->create([
                'date_generation' => now(),
                'periode_debut' => $periodStart,
                'periode_fin' => $periodEnd,
                'devise' => $project->devise,
                'nom_fichier' => $filename,
                'chemin_fichier' => $filePath,
                'instantane_hypotheses' => $analysis,
            ]);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($filePath);
            throw $exception;
        }
    }
}
