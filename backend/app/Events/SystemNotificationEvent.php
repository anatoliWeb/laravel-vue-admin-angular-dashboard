<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SystemNotificationEvent implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public string $type,
        public string $title,
        public string $message,
        public string $createdAt,
    ) {
    }

    public function broadcastOn(): Channel
    {
        // Public foundation channel for Phase 4 smoke tests.
        return new Channel('system.notifications');
    }

    public function broadcastAs(): string
    {
        return 'system.notification';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'created_at' => $this->createdAt,
        ];
    }
}

