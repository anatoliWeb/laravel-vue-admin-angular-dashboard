<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class V1AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_login_success_returns_shared_auth_contract(): void
    {
        User::factory()->create([
            'email' => 'v1auth@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/v1/auth/token', [
            'email' => 'v1auth@example.com',
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user',
                    'permissions',
                    'roles',
                ],
            ])
            ->assertJsonPath('success', true);
    }

    public function test_token_login_invalid_credentials_returns_unauthorized(): void
    {
        User::factory()->create([
            'email' => 'v1auth@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/v1/auth/token', [
            'email' => 'v1auth@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_token_me_returns_user_permissions_and_roles(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this
            ->withToken($token)
            ->getJson('/api/v1/auth/me');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user',
                    'permissions',
                    'roles',
                ],
            ])
            ->assertJsonPath('success', true);
    }

    public function test_token_logout_revokes_current_access_token(): void
    {
        $user = User::factory()->create();
        $newToken = $user->createToken('logout-token');
        $plainTextToken = $newToken->plainTextToken;
        $tokenId = $newToken->accessToken->id;

        $response = $this
            ->withToken($plainTextToken)
            ->postJson('/api/v1/auth/logout');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenId,
        ]);
    }

    public function test_session_login_currently_returns_session_store_error(): void
    {
        User::factory()->create([
            'email' => 'session@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this
            ->withSession([])
            ->post('/api/v1/auth/session/login', [
                'email' => 'session@example.com',
                'password' => 'secret123',
                'remember' => true,
            ], [
                'Accept' => 'application/json',
            ]);

        $response->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Session store not set on request.');
    }

    public function test_session_me_returns_user_permissions_and_roles(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web');

        $response = $this->getJson('/api/v1/auth/session/me');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user',
                    'permissions',
                    'roles',
                ],
            ])
            ->assertJsonPath('success', true);
    }

    public function test_session_logout_currently_returns_session_store_error(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web');

        $response = $this
            ->withSession([])
            ->post('/api/v1/auth/session/logout', [], [
                'Accept' => 'application/json',
            ]);

        $response->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Session store not set on request.');
    }
}
