<?php

namespace Tests\Feature;

use App\Features\Identity\Models\User;
use App\Features\ReferenceData\Models\Category;
use App\Features\ReferenceData\Models\Sector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiRouteCoverageTest extends TestCase
{
    private const TOKEN_NAME = 'route coverage';

    private const PROJECTS_ENDPOINT = '/api/v1/projects';

    private const OPENING_BALANCE_DATE = '2026-01-01';

    use RefreshDatabase;

    public function test_reference_data_and_project_routes_work_for_the_authenticated_user(): void
    {

        $user = User::factory()->create();
        $token = $user->createToken(self::TOKEN_NAME)->plainTextToken;
        $sector = Sector::query()->create(['code' => 'services', 'label' => 'Services']);
        Category::query()->create([
            'sector_id' => $sector->id,
            'type' => 'income',
            'label' => 'Prestations',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/sectors')
            ->assertOk()
            ->assertJsonPath('data.0.id', $sector->id)
            ->assertJsonPath('data.0.label', 'Services');

        $this->withToken($token)
            ->getJson('/api/v1/categories?sector_id='.$sector->id.'&type=income')
            ->assertOk()
            ->assertJsonPath('data.0.label', 'Prestations');

        $this->withToken($token)
            ->getJson(self::PROJECTS_ENDPOINT)
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $created = $this->withToken($token)->postJson(self::PROJECTS_ENDPOINT, [
            'sector_id' => $sector->id,
            'name' => 'Projet initial',
            'launch_date' => '2026-12-01',
            'activity_mode' => 'detailed',
        ]);

        $created->assertCreated()
            ->assertJsonPath('data.name', 'Projet initial')
            ->assertJsonPath('data.activity_mode', 'detailed');

        $projectId = $created->json('data.id');

        $this->withToken($token)
            ->getJson("/api/v1/projects/{$projectId}")
            ->assertOk()
            ->assertJsonPath('data.id', $projectId);

        $this->withToken($token)
            ->putJson("/api/v1/projects/{$projectId}", ['name' => 'Projet remplace'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Projet remplace');

        $this->withToken($token)
            ->patchJson("/api/v1/projects/{$projectId}", ['country' => 'Sénégal'])
            ->assertOk()
            ->assertJsonPath('data.country', 'Sénégal');

        $this->withToken($token)
            ->deleteJson("/api/v1/projects/{$projectId}")
            ->assertNoContent();
    }

    public function test_company_and_nested_finance_routes_support_full_crud(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken(self::TOKEN_NAME)->plainTextToken;
        $sector = Sector::query()->create(['code' => 'commerce', 'label' => 'Commerce']);
        $category = Category::query()->create([
            'sector_id' => $sector->id,
            'type' => 'expense',
            'label' => 'Fournitures',
        ]);

        $companyResponse = $this->withToken($token)->postJson('/api/v1/companies', [
            'sector_id' => $sector->id,
            'name' => 'Entreprise initiale',
            'opening_balance' => 1000,
            'opening_balance_date' => self::OPENING_BALANCE_DATE,
        ]);
        $companyResponse->assertCreated();
        $companyId = $companyResponse->json('data.id');

        $this->withToken($token)
            ->getJson('/api/v1/companies')
            ->assertOk()
            ->assertJsonPath('data.0.id', $companyId);
        $this->withToken($token)
            ->getJson("/api/v1/companies/{$companyId}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Entreprise initiale');
        $this->withToken($token)
            ->putJson("/api/v1/companies/{$companyId}", ['name' => 'Entreprise mise a jour'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Entreprise mise a jour');
        $this->withToken($token)
            ->patchJson("/api/v1/companies/{$companyId}", ['currency' => 'EUR'])
            ->assertOk()
            ->assertJsonPath('data.currency', 'EUR');

        $operationResponse = $this->withToken($token)->postJson(
            "/api/v1/companies/{$companyId}/operations",
            [
                'category_id' => $category->id,
                'amount' => 120,
                'operation_date' => '2026-02-01',
            ],
        );
        $operationResponse->assertCreated();
        $operationId = $operationResponse->json('data.id');
        $this->withToken($token)
            ->getJson("/api/v1/companies/{$companyId}/operations")
            ->assertOk()
            ->assertJsonPath('data.0.id', $operationId);
        $this->withToken($token)
            ->getJson("/api/v1/companies/{$companyId}/operations/{$operationId}")
            ->assertOk()
            ->assertJsonPath('data.amount', '120.00');
        $this->withToken($token)
            ->putJson("/api/v1/companies/{$companyId}/operations/{$operationId}", ['amount' => 150])
            ->assertOk()
            ->assertJsonPath('data.amount', '150.00');
        $this->withToken($token)
            ->patchJson("/api/v1/companies/{$companyId}/operations/{$operationId}", ['description' => 'Achat'])
            ->assertOk()
            ->assertJsonPath('data.description', 'Achat');

        $tagResponse = $this->withToken($token)->postJson("/api/v1/companies/{$companyId}/tags", [
            'label' => 'Urgent',
        ]);
        $tagResponse->assertCreated();
        $tagId = $tagResponse->json('data.id');
        $this->withToken($token)
            ->getJson("/api/v1/companies/{$companyId}/tags")
            ->assertOk()
            ->assertJsonPath('data.0.id', $tagId);
        $this->withToken($token)
            ->getJson("/api/v1/companies/{$companyId}/tags/{$tagId}")
            ->assertOk()
            ->assertJsonPath('data.label', 'Urgent');
        $this->withToken($token)
            ->putJson("/api/v1/companies/{$companyId}/tags/{$tagId}", ['label' => 'Important'])
            ->assertOk()
            ->assertJsonPath('data.label', 'Important');
        $this->withToken($token)
            ->patchJson("/api/v1/companies/{$companyId}/tags/{$tagId}", ['label' => 'Prioritaire'])
            ->assertOk()
            ->assertJsonPath('data.label', 'Prioritaire');

        $budgetResponse = $this->withToken($token)->postJson("/api/v1/companies/{$companyId}/budgets", [
            'year' => 2026,
            'month' => 2,
            'amount' => 500,
        ]);
        $budgetResponse->assertCreated();
        $budgetId = $budgetResponse->json('data.id');
        $this->withToken($token)
            ->getJson("/api/v1/companies/{$companyId}/budgets")
            ->assertOk()
            ->assertJsonPath('data.0.id', $budgetId);
        $this->withToken($token)
            ->getJson("/api/v1/companies/{$companyId}/budgets/{$budgetId}")
            ->assertOk()
            ->assertJsonPath('data.amount', '500.00');
        $this->withToken($token)
            ->putJson("/api/v1/companies/{$companyId}/budgets/{$budgetId}", ['amount' => 600])
            ->assertOk()
            ->assertJsonPath('data.amount', '600.00');
        $this->withToken($token)
            ->patchJson("/api/v1/companies/{$companyId}/budgets/{$budgetId}", ['amount' => 700])
            ->assertOk()
            ->assertJsonPath('data.amount', '700.00');

        $this->withToken($token)
            ->getJson("/api/v1/companies/{$companyId}/dashboard?year=2026")
            ->assertOk()
            ->assertJsonPath('data.totals.expenses', '150.00');

        $this->withToken($token)->deleteJson("/api/v1/companies/{$companyId}/operations/{$operationId}")
            ->assertNoContent();
        $this->withToken($token)->deleteJson("/api/v1/companies/{$companyId}/tags/{$tagId}")
            ->assertNoContent();
        $this->withToken($token)->deleteJson("/api/v1/companies/{$companyId}/budgets/{$budgetId}")
            ->assertNoContent();
        $this->withToken($token)->deleteJson("/api/v1/companies/{$companyId}")
            ->assertNoContent();
    }

    public function test_project_hypotheses_analysis_and_report_routes_work(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $token = $user->createToken(self::TOKEN_NAME)->plainTextToken;
        $sector = Sector::query()->create(['code' => 'services', 'label' => 'Services']);
        $projectResponse = $this->withToken($token)->postJson(self::PROJECTS_ENDPOINT, [
            'sector_id' => $sector->id,
            'name' => 'Projet previsionnel',
            'launch_date' => self::OPENING_BALANCE_DATE,
            'horizon_months' => 6,
            'activity_mode' => 'global',
        ]);
        $projectResponse->assertCreated();
        $projectId = $projectResponse->json('data.id');

        $this->withToken($token)
            ->getJson("/api/v1/projects/{$projectId}/hypotheses")
            ->assertOk()
            ->assertJsonPath('data.funding', null)
            ->assertJsonCount(0, 'data.activity_lines');

        $this->withToken($token)
            ->putJson("/api/v1/projects/{$projectId}/hypotheses", [
                'funding' => [
                    'initial_investment_total' => 6000,
                    'personal_contribution' => 6000,
                ],
                'activity_lines' => [[
                    'type' => 'commerce_global',
                    'designation' => 'Ventes mensuelles',
                    'monthly_revenue' => 1000,
                    'monthly_cost_of_goods_sold' => 250,
                ]],
                'expenses' => [[
                    'nature' => 'fixe',
                    'label' => 'Loyer',
                    'monthly_amount' => 100,
                    'start_month' => 1,
                    'end_month' => 6,
                ]],
            ])
            ->assertOk()
            ->assertJsonPath('data.funding.initial_investment_total', '6000.00')
            ->assertJsonPath('data.activity_lines.0.type', 'COMMERCE_GLOBAL')
            ->assertJsonPath('data.expenses.0.nature', 'FIXE');

        $this->withToken($token)
            ->getJson("/api/v1/projects/{$projectId}/analysis")
            ->assertOk()
            ->assertJsonPath('data.summary.initial_investment', 6000)
            ->assertJsonPath('data.monthly_forecast.0.revenue', 1000);

        $this->withToken($token)
            ->getJson("/api/v1/projects/{$projectId}/reports")
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $reportResponse = $this->withToken($token)
            ->postJson("/api/v1/projects/{$projectId}/reports");
        $reportResponse->assertCreated()
            ->assertJsonPath('data.period_start', self::OPENING_BALANCE_DATE)
            ->assertJsonPath('data.period_end', '2026-06-30');
        $reportId = $reportResponse->json('data.id');
        $filename = $reportResponse->json('data.file_name');

        $this->withToken($token)
            ->getJson("/api/v1/projects/{$projectId}/reports")
            ->assertOk()
            ->assertJsonPath('data.0.id', $reportId);

        $this->withToken($token)
            ->get("/api/v1/projects/{$projectId}/reports/{$reportId}/download")
            ->assertDownload($filename);
    }
}
