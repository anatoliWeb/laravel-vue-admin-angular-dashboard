<?php

namespace App\Jobs\Notifications;

use App\Actions\Notifications\CreateNotificationAction;
use App\Events\Notifications\NotificationCreated;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateNotificationJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * Must remain <= worker timeout.
     */
    public int $timeout = 60;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        public int $userId,
        public string $title,
        public string $message,
        public array $data = [],
    ) {
        $this->onQueue('notifications');
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function handle(CreateNotificationAction $createNotificationAction): void
    {
        $user = User::query()->find($this->userId);

        if (!$user) {
            Log::warning('CreateNotificationJob skipped: user not found', [
                'user_id' => $this->userId,
            ]);
            return;
        }

        $notification = $createNotificationAction->execute(
            $user,
            $this->title,
            $this->message,
            $this->data,
        );

        event(new NotificationCreated(
            notificationId: $notification->id,
            notifiableId: (int) $notification->notifiable_id,
            type: (string) $notification->type,
            title: data_get($notification->data, 'title'),
            message: data_get($notification->data, 'message'),
            actorId: null,
            occurredAt: now()->toIso8601String(),
        ));
    }

    public function failed(Throwable $exception): void
    {
        Log::error('CreateNotificationJob permanently failed', [
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
        ]);
    }
}
