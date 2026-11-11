<?php

namespace Tests\Feature\BusinessFinance;

use App\Features\Identity\Models\User;
use App\Features\ReferenceData\Models\Category;
use App\Features\ReferenceData\Models\Sector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CompanyApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_register_company_operations_and_isolated_records_return_not_found(): void
    {
        $sector = Sector::query()->create(['code' => 'services', 'label' => 'Services']);
        $category = Category::query()->create([
            'sector_id' => $sector->id,
            'type' => 'expense',
            'label' => 'Fournitures',
        ]);
        $owner = User::factory()->create();
        $token = $owner->createToken('test')->plainTextToken;

        $companyResponse = $this->withToken($token)->postJson('/api/v1/companies', [
            'sector_id' => $sector->id,
            'name' => 'Biznova',
            'opening_balance' => 5000,
            'opening_balance_date' => '2026-01-01',
        ]);

        $companyResponse->assertCreated()
            ->assertJsonPath('data.name', 'Biznova')
            ->assertJsonPath('data.currency', 'XAF');

        $companyId = $companyResponse->json('data.id');
        $operationResponse = $this->withToken($token)->postJson("/api/v1/companies/{$companyId}/operations", [
            'category_id' => $category->id,
            'amount' => 1200,
            'operation_date' => '2026-11-10',
        ]);

        $operationResponse->assertCreated()
            ->assertJsonPath('data.amount', '1200.00');

        $this->withToken($token)->postJson("/api/v1/companies/{$companyId}/tags", [
            'label' => 'Factures',
        ])->assertCreated()
            ->assertJsonPath('data.label', 'Factures');

        $this->withToken($token)->postJson("/api/v1/companies/{$companyId}/budgets", [
            'year' => 2026,
            'month' => 11,
            'amount' => 20000,
        ])->assertCreated();

        $this->withToken($token)->postJson("/api/v1/companies/{$companyId}/budgets", [
            'year' => 2026,
            'month' => 11,
            'amount' => 25000,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('month');

        $otherUser = User::factory()->create();
        $otherToken = $otherUser->createToken('other')->plainTextToken;

        Auth::forgetGuards();
        $this->withToken($otherToken)
            ->getJson("/api/v1/companies/{$companyId}/operations/".$operationResponse->json('data.id'))
            ->assertNotFound();
    }
}
