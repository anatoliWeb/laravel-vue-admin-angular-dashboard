<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ActivityLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_create_update_delete_writes_activity_logs(): void
    {
        $actor = User::factory()->create();
        Sanctum::actingAs($actor);

        $created = User::create([
            'name' => 'Activity Target',
            'email' => 'activity-target@example.com',
            'password' => 'password',
        ]);

        $created->update(['name' => 'Activity Updated']);
        $created->delete();

        $this->assertDatabaseHas('activity_logs', ['action' => 'user_created']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'user_updated']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'user_deleted']);
    }

    public function test_token_create_and_delete_writes_activity_logs(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $token = $user->createToken('test-token');
        $token->accessToken->delete();

        $this->assertDatabaseHas('activity_logs', ['action' => 'token_created']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'token_deleted']);
    }
}
