<?php

namespace Tests\Feature\Api;

use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ActivityLoggingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // WHY:
        // This suite verifies persisted audit rows, not queue dispatch behavior.
        // We force sync queue execution here so observer-triggered activity jobs
        // are executed immediately within the same test transaction.
        config()->set('queue.default', 'sync');
    }

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

    public function test_user_update_via_api_writes_single_domain_event_activity_without_password_field(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create([
            'name' => 'Before',
            'email' => 'before-update@example.com',
        ]);

        $editPermission = Permission::firstOrCreate(['name' => 'users.edit']);
        $actor->permissions()->sync([$editPermission->id]);

        Sanctum::actingAs($actor);

        $response = $this->putJson("/api/users/{$target->id}", [
            'name' => 'After',
            'email' => 'after-update@example.com',
            'roles' => [],
            'permissions' => [],
            'denied_permissions' => [],
            'password' => 'new-secret-password',
        ]);

        $response->assertOk();

        $updates = ActivityLog::query()
            ->where('action', 'user_updated')
            ->where('meta->user_id', $target->id)
            ->get();

        $domainEventUpdates = $updates->filter(
            fn (ActivityLog $log): bool => data_get($log->meta, 'source') === 'domain_event'
        );
        $nonDomainUpdates = $updates->filter(
            fn (ActivityLog $log): bool => data_get($log->meta, 'source') !== 'domain_event'
        );

        $this->assertCount(0, $nonDomainUpdates);
        $this->assertGreaterThanOrEqual(1, $domainEventUpdates->count());

        /** @var ActivityLog $domainEventLog */
        $domainEventLog = $domainEventUpdates->first();
        $this->assertNotContains('password', data_get($domainEventLog->meta, 'changed', []));
    }

    public function test_token_api_lifecycle_uses_domain_event_activity_without_plain_token(): void
    {
        $user = User::factory()->create();
        $createPermission = Permission::firstOrCreate(['name' => 'tokens.create']);
        $deletePermission = Permission::firstOrCreate(['name' => 'tokens.delete']);
        $user->permissions()->sync([$createPermission->id, $deletePermission->id]);
        Sanctum::actingAs($user);

        $createResponse = $this->postJson('/api/v1/tokens', [
            'name' => 'Activity API Token',
        ]);
        $createResponse->assertCreated();

        $createdTokenId = (int) $createResponse->json('data.access_token.id');

        $this->deleteJson("/api/v1/tokens/{$createdTokenId}")
            ->assertOk();

        $createdLogs = ActivityLog::query()
            ->where('action', 'token_created')
            ->where('meta->token_id', $createdTokenId)
            ->get();

        $revokedLogs = ActivityLog::query()
            ->where('action', 'token_deleted')
            ->where('meta->token_id', $createdTokenId)
            ->get();

        $this->assertGreaterThanOrEqual(1, $createdLogs->count());
        $this->assertGreaterThanOrEqual(1, $revokedLogs->count());

        foreach ($createdLogs as $log) {
            $this->assertSame('domain_event', data_get($log->meta, 'source'));
            $this->assertNull(data_get($log->meta, 'token'));
            $this->assertNull(data_get($log->meta, 'plain_text_token'));
        }

        foreach ($revokedLogs as $log) {
            $this->assertSame('domain_event', data_get($log->meta, 'source'));
            $this->assertNull(data_get($log->meta, 'token'));
            $this->assertNull(data_get($log->meta, 'plain_text_token'));
        }
    }
}
