<?php

namespace Tests\Feature\Api;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RealtimeChannelAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_authorize_private_system_notifications_channel(): void
    {
        $response = $this->postJson('/broadcasting/auth', [
            'socket_id' => '123.456',
            'channel_name' => 'private-system.notifications',
        ]);

        $this->assertContains($response->status(), [401, 403]);
    }

    public function test_authenticated_user_without_notifications_view_permission_cannot_authorize_private_channel(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/broadcasting/auth', [
            'socket_id' => '123.456',
            'channel_name' => 'private-system.notifications',
        ])->assertForbidden();
    }

    public function test_authenticated_user_with_notifications_view_permission_can_authorize_private_channel(): void
    {
        $user = User::factory()->create();
        $permission = Permission::firstOrCreate(['name' => 'notifications.view']);
        $user->permissions()->sync([$permission->id]);

        Sanctum::actingAs($user);

        $this->postJson('/broadcasting/auth', [
            'socket_id' => '123.456',
            'channel_name' => 'private-system.notifications',
        ])->assertOk()
            ->assertJsonStructure(['auth']);
    }
}

