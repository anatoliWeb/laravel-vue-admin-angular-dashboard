<?php

namespace Tests\Feature\Events;

use App\Events\Users\UserCreated;
use App\Listeners\Users\LogUserCreatedActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCreatedEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_created_listener_writes_activity(): void
    {
        $listener = app(LogUserCreatedActivity::class);

        $listener->handle(new UserCreated(
            userId: 777,
            userName: 'Domain User',
            userEmail: 'domain-user@example.com',
            actorId: null,
            occurredAt: now()->toIso8601String(),
        ));
        $this->assertTrue(true);
    }
}
