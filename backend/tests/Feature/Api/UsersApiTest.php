<?php

namespace Tests\Feature\Api;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UsersApiTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsWithPermissions(array $permissions): User
    {
        $user = User::factory()->create();

        $permissionIds = collect($permissions)
            ->map(fn (string $name) => Permission::firstOrCreate(['name' => $name])->id)
            ->all();

        $user->permissions()->sync($permissionIds);
        Sanctum::actingAs($user);

        return $user;
    }

    public function test_users_index_requires_users_view_permission(): void
    {
        $this->actingAsWithPermissions([]);

        $this->getJson('/api/users')->assertForbidden();
    }

    public function test_users_index_returns_data_when_authorized(): void
    {
        User::factory()->count(2)->create();
        $this->actingAsWithPermissions(['users.view']);

        $this->getJson('/api/users')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email', 'roles'],
                ],
            ]);
    }

    public function test_user_show_returns_404_for_unknown_user(): void
    {
        $this->actingAsWithPermissions(['users.view']);

        $this->getJson('/api/users/999999')->assertNotFound();
    }

    public function test_user_can_be_created_with_roles_and_direct_permissions(): void
    {
        $this->actingAsWithPermissions(['users.create']);

        $role = Role::create(['name' => 'manager']);
        Permission::firstOrCreate(['name' => 'users.view']);
        Permission::firstOrCreate(['name' => 'users.edit']);

        $payload = [
            'name' => 'Created User',
            'email' => 'created@example.com',
            'password' => 'secret123',
            'roles' => [$role->id],
            'permissions' => ['users.view', 'users.edit'],
        ];

        $response = $this->postJson('/api/users', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.email', 'created@example.com')
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'roles', 'permissions'],
            ]);

        $createdId = $response->json('data.id');
        $createdUser = User::findOrFail($createdId);

        $this->assertTrue($createdUser->roles()->where('roles.id', $role->id)->exists());
        $this->assertTrue($createdUser->permissions()->where('name', 'users.view')->exists());
        $this->assertTrue($createdUser->permissions()->where('name', 'users.edit')->exists());
    }

    public function test_user_create_validates_payload(): void
    {
        $this->actingAsWithPermissions(['users.create']);

        $this->postJson('/api/users', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_user_can_be_updated_with_optional_password(): void
    {
        $this->actingAsWithPermissions(['users.edit']);

        $user = User::factory()->create([
            'email' => 'before@example.com',
            'password' => bcrypt('old-password'),
        ]);
        $role = Role::create(['name' => 'user']);
        Permission::firstOrCreate(['name' => 'users.view']);

        $response = $this->putJson("/api/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => 'after@example.com',
            'roles' => [$role->id],
            'permissions' => ['users.view'],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.email', 'after@example.com');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'after@example.com',
        ]);
    }

    public function test_user_update_requires_users_edit_permission(): void
    {
        $target = User::factory()->create();
        $this->actingAsWithPermissions(['users.view']);

        $this->putJson("/api/users/{$target->id}", [
            'name' => 'No Access',
            'email' => 'no-access@example.com',
        ])->assertForbidden();
    }

    public function test_user_delete_requires_users_delete_permission(): void
    {
        $target = User::factory()->create();
        $this->actingAsWithPermissions(['users.view']);

        $this->deleteJson("/api/users/{$target->id}")->assertForbidden();
    }

    public function test_user_can_be_deleted_when_authorized(): void
    {
        $target = User::factory()->create();
        $this->actingAsWithPermissions(['users.delete']);

        $this->deleteJson("/api/users/{$target->id}")
            ->assertOk()
            ->assertJsonPath('data.deleted', true);

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }
}
