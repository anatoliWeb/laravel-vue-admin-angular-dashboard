<?php

namespace Tests\Feature\Api;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_and_session_me_return_consistent_auth_contract_payload(): void
    {
        $user = User::factory()->create([
            'email' => 'contract@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $role = Role::create(['name' => 'contract-role']);

        $rolePermission = Permission::firstOrCreate(['name' => 'reports.view']);
        $directPermission = Permission::firstOrCreate(['name' => 'settings.view']);
        $deniedPermission = Permission::firstOrCreate(['name' => 'users.delete']);

        $role->permissions()->sync([$rolePermission->id, $deniedPermission->id]);
        $user->roles()->sync([$role->id]);
        $user->permissions()->sync([$directPermission->id]);
        $user->deniedPermissions()->sync([$deniedPermission->id]);

        $tokenLogin = $this->postJson('/api/v1/auth/token', [
            'email' => 'contract@example.com',
            'password' => 'secret123',
        ]);

        $tokenLogin->assertOk()
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

        $plainToken = $tokenLogin->json('data.token');
        $this->assertIsString($plainToken);
        $this->assertNotEmpty($plainToken);

        $tokenMe = $this
            ->withToken((string) $plainToken)
            ->getJson('/api/v1/auth/me');

        $tokenMe->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user',
                    'permissions',
                    'roles',
                ],
            ]);

        $sessionLogin = $this->postJson('/api/v1/auth/session/login', [
            'email' => 'contract@example.com',
            'password' => 'secret123',
            'remember' => true,
        ]);

        $sessionLogin->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user',
                    'permissions',
                    'roles',
                ],
            ]);

        $sessionMe = $this->getJson('/api/v1/auth/session/me');

        $sessionMe->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user',
                    'permissions',
                    'roles',
                ],
            ]);

        $tokenPayload = $tokenMe->json('data');
        $sessionPayload = $sessionMe->json('data');

        $this->assertSame($tokenPayload['user']['id'], $sessionPayload['user']['id']);
        $this->assertSame($tokenPayload['user']['email'], $sessionPayload['user']['email']);

        $tokenRoles = $tokenPayload['roles'];
        $sessionRoles = $sessionPayload['roles'];
        sort($tokenRoles);
        sort($sessionRoles);
        $this->assertSame($tokenRoles, $sessionRoles);
        $this->assertContains('contract-role', $tokenRoles);

        $tokenPermissions = $tokenPayload['permissions'];
        $sessionPermissions = $sessionPayload['permissions'];
        sort($tokenPermissions);
        sort($sessionPermissions);
        $this->assertSame($tokenPermissions, $sessionPermissions);

        $this->assertContains('reports.view', $tokenPermissions);
        $this->assertContains('settings.view', $tokenPermissions);
        $this->assertNotContains('users.delete', $tokenPermissions);
    }

    public function test_auth_payload_permissions_refresh_after_user_rbac_update(): void
    {
        $user = User::factory()->create([
            'email' => 'cache-refresh@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $role = Role::create(['name' => 'cache-role']);
        $rolePermission = Permission::firstOrCreate(['name' => 'reports.view']);
        $directPermission = Permission::firstOrCreate(['name' => 'settings.view']);
        $deniedPermission = Permission::firstOrCreate(['name' => 'users.delete']);
        $newDirectPermission = Permission::firstOrCreate(['name' => 'tokens.view']);

        $role->permissions()->sync([$rolePermission->id, $deniedPermission->id]);
        $user->roles()->sync([$role->id]);
        $user->permissions()->sync([$directPermission->id]);
        $user->deniedPermissions()->sync([$deniedPermission->id]);

        $tokenLogin = $this->postJson('/api/v1/auth/token', [
            'email' => 'cache-refresh@example.com',
            'password' => 'secret123',
        ])->assertOk();

        $plainToken = (string) $tokenLogin->json('data.token');

        $initialMe = $this->withToken($plainToken)->getJson('/api/v1/auth/me');
        $initialMe->assertOk();

        $initialPermissions = $initialMe->json('data.permissions');
        $this->assertContains('reports.view', $initialPermissions);
        $this->assertContains('settings.view', $initialPermissions);
        $this->assertNotContains('users.delete', $initialPermissions);

        $operator = User::factory()->create();
        $this->actingAs($operator, 'web');

        /** @var UserService $userService */
        $userService = app(UserService::class);
        $userService->update($user->id, [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => [$role->id],
            'permissions' => ['tokens.view'],
            'denied_permissions' => [],
        ]);
        Auth::guard('web')->logout();

        $updatedMe = $this->withToken($plainToken)->getJson('/api/v1/auth/me');
        $updatedMe->assertOk();

        $updatedPermissions = $updatedMe->json('data.permissions');
        $this->assertContains('tokens.view', $updatedPermissions);
        $this->assertNotContains('settings.view', $updatedPermissions);
    }
}
