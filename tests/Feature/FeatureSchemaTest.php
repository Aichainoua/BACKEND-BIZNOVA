<?php

namespace Tests\Feature;

use App\Features\BusinessFinance\Models\Company;
use App\Features\BusinessFinance\Models\MonthlyBudget;
use App\Features\Identity\Models\User;
use App\Features\ReferenceData\Models\Category;
use App\Features\ReferenceData\Models\Sector;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FeatureSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_monthly_budgets_are_unique_per_company_and_month(): void
    {
        $company = $this->createCompany();

        $company->budgets()->create([
            'year' => 2026,
            'month' => 11,
            'amount' => 150000,
        ]);

        $this->expectException(QueryException::class);

        $company->budgets()->create([
            'year' => 2026,
            'month' => 11,
            'amount' => 160000,
        ]);
    }

    public function test_operation_and_tag_must_belong_to_the_same_company(): void
    {
        $sector = Sector::query()->create(['code' => 'services', 'label' => 'Services']);
        $category = Category::query()->create([
            'sector_id' => $sector->id,
            'type' => 'expense',
            'label' => 'Fournitures',
        ]);
        $company = $this->createCompany($sector);
        $otherCompany = $this->createCompany($sector);

        $operation = $company->operations()->create([
            'category_id' => $category->id,
            'amount' => 1200,
            'operation_date' => '2026-11-10',
        ]);
        $tag = $otherCompany->tags()->create(['label' => 'Personnel']);

        $this->expectException(QueryException::class);

        $operation->tags()->attach($tag->id, [
            'company_id' => $company->id,
            'associated_at' => now(),
        ]);
    }

    public function test_feature_models_are_migrated_and_expose_their_relations(): void
    {
        $company = $this->createCompany();
        $tag = $company->tags()->create(['label' => 'Important']);

        $this->assertSame($company->id, $tag->company->id);
        $this->assertInstanceOf(MonthlyBudget::class, $company->budgets()->make());
        foreach ([
            'users',
            'sectors',
            'categories',
            'projects',
            'project_fundings',
            'project_activity_lines',
            'project_expenses',
            'project_reports',
            'companies',
            'financial_operations',
            'tags',
            'operation_tag',
            'monthly_budgets',
            'monthly_reports',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Expected table [{$table}] to exist.");
        }
    }

    private function createCompany(?Sector $sector = null): Company
    {
        $sector ??= Sector::query()->create([
            'code' => 'commerce',
            'label' => 'Commerce',
        ]);
        $user = User::factory()->create();

        return $user->companies()->create([
            'sector_id' => $sector->id,
            'name' => 'Biznova',
            'currency' => 'XAF',
            'opening_balance' => 0,
            'opening_balance_date' => '2026-01-01',
        ]);
    }
}
