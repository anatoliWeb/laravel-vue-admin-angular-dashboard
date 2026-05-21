<?php

namespace App\Services\Chat;

use App\Models\ChatWebhookDelivery;
use App\Models\ChatWebhookEndpoint;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ChatWebhookDeliveryService
{
    public function createDelivery(ChatWebhookEndpoint $endpoint, string $eventType, array $payload): ChatWebhookDelivery
    {
        return ChatWebhookDelivery::query()->create([
            'webhook_endpoint_id' => $endpoint->id,
            'conversation_id' => data_get($payload, 'conversation_id'),
            'message_id' => data_get($payload, 'message_id'),
            'event' => $eventType,
            'delivery_uuid' => (string) Str::uuid(),
            'payload' => $payload,
            'status' => 'pending',
            'attempts' => 0,
            'signature' => null,
            'metadata' => [
                'source' => 'chat_webhook_delivery_service',
            ],
        ]);
    }

    public function markAttempted(ChatWebhookDelivery $delivery, ?int $statusCode = null, ?string $error = null): ChatWebhookDelivery
    {
        $delivery->attempts = (int) $delivery->attempts + 1;
        $delivery->response_status = $statusCode;
        $delivery->error_message = $error !== null ? mb_substr($error, 0, 65535) : null;
        $delivery->status = $error === null ? 'sent' : 'failed';
        $delivery->sent_at = $error === null ? now() : $delivery->sent_at;
        $delivery->failed_at = $error !== null ? now() : null;
        $delivery->save();

        return $delivery->fresh();
    }

    public function scheduleRetry(ChatWebhookDelivery $delivery): ChatWebhookDelivery
    {
        $maxAttempts = max((int) config('chat.webhooks.max_attempts', 5), 1);
        $nextAttempts = (int) $delivery->attempts + 1;

        if ($nextAttempts > $maxAttempts) {
            $delivery->status = 'failed';
            $delivery->next_retry_at = null;
            $delivery->failed_at = now();
            $delivery->save();

            return $delivery->fresh();
        }

        $delivery->attempts = $nextAttempts;
        $delivery->status = 'retrying';
        $delivery->next_retry_at = $this->calculateNextAttemptAt($nextAttempts);
        $delivery->save();

        return $delivery->fresh();
    }

    public function calculateNextAttemptAt(int $attempts): ?Carbon
    {
        $backoff = (array) config('chat.webhooks.retry_backoff_seconds', [60, 300, 900, 3600]);
        $index = max($attempts - 1, 0);
        $seconds = $backoff[$index] ?? end($backoff);
        if (! is_int($seconds)) {
            $seconds = (int) $seconds;
        }
        if ($seconds <= 0) {
            return null;
        }

        return now()->addSeconds($seconds);
    }
}

