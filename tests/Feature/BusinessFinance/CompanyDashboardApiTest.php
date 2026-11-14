<?php

namespace Tests\Feature\BusinessFinance;

use App\Features\BusinessFinance\Models\Company;
use App\Features\Identity\Models\User;
use App\Features\ReferenceData\Models\Category;
use App\Features\ReferenceData\Models\Sector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyDashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_retrieve_company_dashboard_aggregates_for_a_year(): void
    {
        $user = User::factory()->create();
        $company = $this->createCompany($user);
        $incomeCategory = Category::query()->create([
            'sector_id' => $company->sector_id,
            'type' => 'income',
            'label' => 'Ventes',
        ]);
        $expenseCategory = Category::query()->create([
            'sector_id' => $company->sector_id,
            'type' => 'expense',
            'label' => 'Fournitures',
        ]);

        $company->operations()->create([
            'category_id' => $incomeCategory->id,
            'amount' => 5000,
            'operation_date' => '2026-01-15',
        ]);
        $company->operations()->create([
            'category_id' => $incomeCategory->id,
            'amount' => 1000,
            'operation_date' => '2026-02-15',
        ]);
        $expenseInJanuary = $company->operations()->create([
            'category_id' => $expenseCategory->id,
            'amount' => 1500,
            'operation_date' => '2026-01-20',
        ]);
        $company->operations()->create([
            'category_id' => $expenseCategory->id,
            'amount' => 2500,
            'operation_date' => '2026-02-20',
        ]);
        $company->operations()->create([
            'category_id' => $expenseCategory->id,
            'amount' => 20000,
            'operation_date' => '2025-12-20',
        ]);
        $tag = $company->tags()->create(['label' => 'Bureau']);
        $expenseInJanuary->tags()->attach($tag->id, [
            'company_id' => $company->id,
            'associated_at' => '2026-01-20 12:00:00',
        ]);
        $company->budgets()->create(['year' => 2026, 'month' => 1, 'amount' => 2000]);
        $company->budgets()->create(['year' => 2026, 'month' => 2, 'amount' => 2200]);
        $otherCompany = $this->createCompany(
            User::factory()->create(),
            Sector::query()->findOrFail($company->sector_id),
        );
        $otherIncomeCategory = Category::query()->create([
            'sector_id' => $otherCompany->sector_id,
            'type' => 'income',
            'label' => 'Autres ventes',
        ]);
        $otherCompany->operations()->create([
            'category_id' => $otherIncomeCategory->id,
            'amount' => 50000,
            'operation_date' => '2026-03-15',
        ]);

        $response = $this->withToken($user->createToken('dashboard test')->plainTextToken)
            ->getJson("/api/v1/companies/{$company->id}/dashboard?year=2026");

        $response->assertOk()
            ->assertJsonPath('data.year', 2026)
            ->assertJsonPath('data.totals.income', '6000.00')
            ->assertJsonPath('data.totals.expenses', '4000.00')
            ->assertJsonPath('data.totals.net', '2000.00')
            ->assertJsonPath('data.monthly_trends.0.income', '5000.00')
            ->assertJsonPath('data.monthly_trends.0.expenses', '1500.00')
            ->assertJsonPath('data.monthly_trends.1.net', '-1500.00')
            ->assertJsonPath('data.category_breakdown.0.category', 'Fournitures')
            ->assertJsonPath('data.category_breakdown.0.total', '4000.00')
            ->assertJsonPath('data.budget_comparison.0.budgeted', '2000.00')
            ->assertJsonPath('data.budget_comparison.0.expenses', '1500.00')
            ->assertJsonPath('data.budget_comparison.1.budgeted', '2200.00')
            ->assertJsonPath('data.budget_comparison.1.expenses', '2500.00')
            ->assertJsonPath('data.tag_breakdown.0.label', 'Bureau')
            ->assertJsonPath('data.tag_breakdown.0.expenses', '1500.00');

        $this->assertCount(12, $response->json('data.monthly_trends'));
        $this->assertCount(12, $response->json('data.budget_comparison'));
    }

    public function test_a_company_dashboard_requires_authentication(): void
    {
        $company = $this->createCompany(User::factory()->create());

        $this->getJson("/api/v1/companies/{$company->id}/dashboard")->assertUnauthorized();
    }

    public function test_a_user_cannot_retrieve_another_users_company_dashboard(): void
    {
        $company = $this->createCompany(User::factory()->create());
        $otherUser = User::factory()->create();
        $token = $otherUser->createToken('other user')->plainTextToken;

        $this->withToken($token)
            ->getJson("/api/v1/companies/{$company->id}/dashboard")
            ->assertNotFound();
    }

    public function test_a_company_dashboard_rejects_an_invalid_year(): void
    {
        $user = User::factory()->create();
        $company = $this->createCompany($user);
        $token = $user->createToken('dashboard test')->plainTextToken;

        $this->withToken($token)
            ->getJson("/api/v1/companies/{$company->id}/dashboard?year=1999")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('year');
    }

    public function test_a_company_dashboard_defaults_to_the_current_year(): void
    {
        $this->travelTo(now()->setDate(2026, 6, 15));
        $user = User::factory()->create();
        $company = $this->createCompany($user);
        $token = $user->createToken('dashboard test')->plainTextToken;

        $this->withToken($token)
            ->getJson("/api/v1/companies/{$company->id}/dashboard")
            ->assertOk()
            ->assertJsonPath('data.year', 2026);
    }

    private function createCompany(User $user, ?Sector $sector = null): Company
    {
        $sector ??= Sector::query()->create([
            'code' => 'commerce',
            'label' => 'Commerce',
        ]);

        return $user->companies()->create([
            'sector_id' => $sector->id,
            'name' => 'Biznova',
            'currency' => 'XAF',
            'opening_balance' => 0,
            'opening_balance_date' => '2026-01-01',
        ]);
    }
}
