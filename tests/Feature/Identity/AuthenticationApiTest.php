<?php

namespace Tests\Feature\Identity;

use App\Features\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_register_and_receive_a_bearer_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'first_name' => 'Aïcha',
            'last_name' => 'Diallo',
            'email' => 'AICHA@example.com',
            'password' => 'Correct-Horse-123',
            'password_confirmation' => 'Correct-Horse-123',
            'device_name' => 'test client',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.user.first_name', 'Aïcha')
            ->assertJsonPath('data.user.last_name', 'Diallo')
            ->assertJsonPath('data.user.email', 'aicha@example.com')
            ->assertJsonPath('data.token_type', 'Bearer');

        $user = User::query()->where('email', 'aicha@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('Correct-Horse-123', $user->password));

        $this->withToken($response->json('data.access_token'))
            ->getJson('/api/v1/auth/profile')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_login_rejects_invalid_credentials_and_issues_a_token_for_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => 'Correct-Horse-123',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'wrong-password',
            'device_name' => 'browser',
        ])->assertUnauthorized()
            ->assertJsonPath('message', 'Les identifiants sont invalides.');

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'Correct-Horse-123',
            'device_name' => 'browser',
        ])->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.token_type', 'Bearer');
    }

    public function test_profile_can_be_updated_and_logout_revokes_the_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test client')->plainTextToken;

        $this->withToken($token)
            ->patchJson('/api/v1/auth/profile', [
                'first_name' => 'Nouveau',
                'email' => $user->email,
            ])
            ->assertOk()
            ->assertJsonPath('data.first_name', 'Nouveau');

        $this->postJson('/api/v1/auth/logout')->assertNoContent();

        Auth::forgetGuards();
        $this->getJson('/api/v1/auth/profile')->assertUnauthorized();

        $this->assertCount(0, $user->tokens()->get());
    }

    public function test_protected_profile_requires_a_bearer_token(): void
    {
        $this->getJson('/api/v1/auth/profile')->assertUnauthorized();
    }

    public function test_registration_validates_password_confirmation(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'first_name' => 'Aïcha',
            'last_name' => 'Diallo',
            'email' => 'aicha@example.com',
            'password' => 'Correct-Horse-123',
            'password_confirmation' => 'different-password',
            'device_name' => 'test client',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }
}
