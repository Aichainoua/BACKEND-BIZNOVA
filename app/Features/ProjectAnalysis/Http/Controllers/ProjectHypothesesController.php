<?php

namespace App\Features\ProjectAnalysis\Http\Controllers;

use App\Features\ProjectAnalysis\Http\Requests\SaveProjectHypothesesRequest;
use App\Features\ProjectAnalysis\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectHypothesesController extends Controller
{
    public function show(Project $project): JsonResponse
    {
        $project = Auth::user()
            ->projects()
            ->with(['funding', 'activityLines', 'expenses'])
            ->findOrFail($project->id_projet);

        return response()->json([
            'data' => $this->formatHypotheses($project),
        ]);
    }

    public function update(
        SaveProjectHypothesesRequest $request,
        Project $project
    ): JsonResponse {
        $project = Auth::user()
            ->projects()
            ->findOrFail($project->id_projet);

        $data = $request->validated();

        foreach ($data['expenses'] as $expense) {
            if (
                isset($expense['end_month'])
                && $expense['end_month'] > $project->horizon_mois
            ) {
                throw ValidationException::withMessages([
                    'expenses' => [
                        'Le mois de fin d’une charge ne peut pas dépasser l’horizon du projet.',
                    ],
                ]);
            }
        }

        DB::transaction(function () use ($project, $data): void {
            $funding = $data['funding'];

            $project->funding()->updateOrCreate([], [
                'investissement_initial_total' =>
                    $funding['initial_investment_total'],

                'apport_personnel' =>
                    $funding['personal_contribution'] ?? 0,

                'financement_hors_emprunt' =>
                    $funding['non_loan_funding'] ?? 0,

                'montant_emprunt' =>
                    $funding['loan_amount'] ?? null,

                'taux_interet_annuel' =>
                    $funding['annual_interest_rate'] ?? 0,

                'duree_emprunt_mois' =>
                    $funding['loan_duration_months'] ?? null,

                'frequence_remboursement' =>
                    $funding['repayment_frequency'] ?? null,

                'differe_mois' =>
                    $funding['deferment_months'] ?? 0,
            ]);

            $project->activityLines()->delete();

            $activityLines = array_map(
                fn (array $line): array => [
                    'type_ligne' => $line['type'],
                    'designation' => $line['designation'],
                    'ca_mensuel_saisi' => $line['monthly_revenue'] ?? null,
                    'cogs_mensuel_saisi' => $line['monthly_cost_of_goods_sold'] ?? null,
                    'prix_unitaire' => $line['unit_price'] ?? null,
                    'volume_mensuel' => $line['monthly_volume'] ?? null,
                    'cout_direct_unitaire' => $line['unit_direct_cost'] ?? null,
                    'prix_abonnement_mensuel' => $line['monthly_subscription_price'] ?? null,
                    'nouveaux_abonnes_mois' => $line['new_subscribers_per_month'] ?? null,
                    'abonnes_actifs' => $line['active_subscribers'] ?? null,
                ],
                $data['activity_lines']
            );

            $project->activityLines()->createMany($activityLines);

            $project->expenses()->delete();

            $expenses = array_map(
                fn (array $expense): array => [
                    'nature_charge' => $expense['nature'],
                    'libelle' => $expense['label'],
                    'montant_mensuel' => $expense['monthly_amount'],
                    'date_debut_mois' => $expense['start_month'],
                    'date_fin_mois' => $expense['end_month'] ?? null,
                ],
                $data['expenses']
            );

            if (!empty($expenses)) {
                $project->expenses()->createMany($expenses);
            }
        });

        $project->load(['funding', 'activityLines', 'expenses']);

        return response()->json([
            'message' => 'Les hypothèses du projet ont été enregistrées.',
            'data' => $this->formatHypotheses($project),
        ]);
    }

    private function formatHypotheses(Project $project): array
    {
        $funding = $project->funding;

        return [
            'funding' => $funding ? [
                'initial_investment_total' => $funding->investissement_initial_total,
                'personal_contribution' => $funding->apport_personnel,
                'non_loan_funding' => $funding->financement_hors_emprunt,
                'loan_amount' => $funding->montant_emprunt,
                'annual_interest_rate' => $funding->taux_interet_annuel,
                'loan_duration_months' => $funding->duree_emprunt_mois,
                'repayment_frequency' => $funding->frequence_remboursement,
                'deferment_months' => $funding->differe_mois,
            ] : null,

            'activity_lines' => $project->activityLines->map(fn ($line) => [
                'id' => $line->id_ligne_activite,
                'type' => $line->type_ligne,
                'designation' => $line->designation,
                'monthly_revenue' => $line->ca_mensuel_saisi,
                'monthly_cost_of_goods_sold' => $line->cogs_mensuel_saisi,
                'unit_price' => $line->prix_unitaire,
                'monthly_volume' => $line->volume_mensuel,
                'unit_direct_cost' => $line->cout_direct_unitaire,
                'monthly_subscription_price' => $line->prix_abonnement_mensuel,
                'new_subscribers_per_month' => $line->nouveaux_abonnes_mois,
                'active_subscribers' => $line->abonnes_actifs,
            ])->values(),

            'expenses' => $project->expenses->map(fn ($expense) => [
                'id' => $expense->id_poste_charge,
                'nature' => $expense->nature_charge,
                'label' => $expense->libelle,
                'monthly_amount' => $expense->montant_mensuel,
                'start_month' => $expense->date_debut_mois,
                'end_month' => $expense->date_fin_mois,
            ])->values(),
        ];
    }
}
