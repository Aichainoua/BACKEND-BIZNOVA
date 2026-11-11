<?php

namespace Tests\Feature\ProjectAnalysis;

use App\Features\Identity\Models\User;
use App\Features\ReferenceData\Models\Sector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_create_a_project_and_cannot_read_another_users_project(): void
    {
        $sector = Sector::query()->create(['code' => 'commerce', 'label' => 'Commerce']);
        $owner = User::factory()->create();
        $token = $owner->createToken('test')->plainTextToken;

        $created = $this->withToken($token)->postJson('/api/v1/projects', [
            'sector_id' => $sector->id,
            'name' => 'Projet pilote',
            'launch_date' => '2026-12-01',
            'horizon_months' => 12,
            'currency' => 'XAF',
            'activity_mode' => 'detailed',
        ]);

        $created->assertCreated()
            ->assertJsonPath('data.name', 'Projet pilote')
            ->assertJsonPath('data.horizon_months', 12);

        $otherUser = User::factory()->create();
        $otherToken = $otherUser->createToken('other')->plainTextToken;

        Auth::forgetGuards();
        $this->withToken($otherToken)
            ->getJson('/api/v1/projects/'.$created->json('data.id'))
            ->assertNotFound();
    }

    public function test_project_projection_horizon_is_limited_to_six_through_sixty_months(): void
    {
        $sector = Sector::query()->create(['code' => 'commerce', 'label' => 'Commerce']);
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/projects', [
            'sector_id' => $sector->id,
            'name' => 'Projet pilote',
            'launch_date' => '2026-12-01',
            'horizon_months' => 5,
            'activity_mode' => 'detailed',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('horizon_months');
    }
}
