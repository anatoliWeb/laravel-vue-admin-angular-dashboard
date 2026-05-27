<?php

namespace App\Jobs\Chat;

use App\Models\ChatWebhookDelivery;
use App\Services\Chat\ChatWebhookDeliveryService;
use App\Services\Chat\ChatWebhookSigningService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class DeliverChatWebhookJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 15;

    public function __construct(
        public int $deliveryId,
    ) {
        $this->onQueue('webhooks');
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [5, 15, 30];
    }

    public function handle(
        ChatWebhookDeliveryService $deliveryService,
        ChatWebhookSigningService $signingService
    ): void {
        $startedAt = microtime(true);
        $delivery = ChatWebhookDelivery::query()
            ->with(['endpoint' => fn ($q) => $q->withTrashed()])
            ->find($this->deliveryId);

        if (! $delivery) {
            $this->logQueueEvent('warning', 'queue.webhooks.delivery.missing', [
                'delivery_id' => $this->deliveryId,
            ]);
            return;
        }

        $baseContext = $this->buildSafeContext($delivery);
        $this->logQueueEvent('info', 'queue.webhooks.delivery.started', $baseContext);

        if (in_array($delivery->status, ['sent', 'failed', 'cancelled'], true)) {
            $this->logQueueEvent('info', 'queue.webhooks.delivery.skipped_terminal_state', $baseContext + [
                'status' => $delivery->status,
            ]);
            return;
        }

        $endpoint = $delivery->endpoint;
        if (! $endpoint || $endpoint->trashed() || ! $endpoint->is_active || $endpoint->status !== 'active') {
            $deliveryService->markCancelled($delivery, 'Endpoint inactive or deleted');
            $this->logQueueEvent('warning', 'queue.webhooks.delivery.cancelled', $baseContext + [
                'status' => 'cancelled',
                'duration_ms' => $this->durationMs($startedAt),
            ]);
            return;
        }

        $payloadJson = json_encode($delivery->payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payloadJson === false) {
            $deliveryService->markFailed($delivery, 'Invalid webhook payload');
            $this->logQueueEvent('error', 'queue.webhooks.delivery.invalid_payload', $baseContext + [
                'status' => 'failed',
                'duration_ms' => $this->durationMs($startedAt),
            ]);
            return;
        }

        $signed = $signingService->signPayload($payloadJson, (string) $endpoint->secret);
        $delivery->signature = $signed['signature'];
        $delivery->save();

        $signatureHeader = (string) config('chat.webhooks.signature_header', 'X-Chat-Signature');
        $timestampHeader = (string) config('chat.webhooks.timestamp_header', 'X-Chat-Timestamp');

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    $signatureHeader => $signed['signature'],
                    $timestampHeader => (string) $signed['timestamp'],
                    'User-Agent' => 'LaravelChatWebhook/1.0',
                ])
                ->post($endpoint->url, $delivery->payload);

            if ($response->successful()) {
                $body = $response->json();
                $safeBody = is_array($body) ? $this->sanitizeResponseBody($body) : null;
                $deliveryService->markSucceeded($delivery, $response->status(), $safeBody);
                $this->logQueueEvent('info', 'queue.webhooks.delivery.completed', $baseContext + [
                    'status' => 'sent',
                    'response_status' => $response->status(),
                    'duration_ms' => $this->durationMs($startedAt),
                ]);
                return;
            }

            $delivery = $deliveryService->markFailed($delivery, 'HTTP '.$response->status());
            $delivery->response_status = $response->status();
            $delivery->response_body = is_array($response->json())
                ? $this->sanitizeResponseBody($response->json())
                : null;
            $delivery->save();

            if ((int) $delivery->attempts >= (int) config('chat.webhooks.max_attempts', 5)) {
                $this->logQueueEvent('error', 'queue.webhooks.delivery.failed', $baseContext + [
                    'status' => 'failed',
                    'response_status' => $response->status(),
                    'duration_ms' => $this->durationMs($startedAt),
                ]);
                return;
            }
            $deliveryService->scheduleRetry($delivery);
            $this->logQueueEvent('warning', 'queue.webhooks.delivery.retry_scheduled', $baseContext + [
                'status' => 'retrying',
                'response_status' => $response->status(),
                'duration_ms' => $this->durationMs($startedAt),
            ]);
        } catch (Throwable $e) {
            $delivery = $deliveryService->markFailed($delivery, 'Webhook delivery exception');

            if ((int) $delivery->attempts >= (int) config('chat.webhooks.max_attempts', 5)) {
                $this->logQueueEvent('error', 'queue.webhooks.delivery.exception_final', $baseContext + [
                    'status' => 'failed',
                    'duration_ms' => $this->durationMs($startedAt),
                    'error_class' => $e::class,
                    'error_summary' => mb_substr($e->getMessage(), 0, 255),
                ]);
                return;
            }
            $deliveryService->scheduleRetry($delivery);
            $this->logQueueEvent('warning', 'queue.webhooks.delivery.exception_retry', $baseContext + [
                'status' => 'retrying',
                'duration_ms' => $this->durationMs($startedAt),
                'error_class' => $e::class,
                'error_summary' => mb_substr($e->getMessage(), 0, 255),
            ]);
        }
    }

    /**
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    private function sanitizeResponseBody(array $body): array
    {
        unset($body['secret'], $body['token'], $body['signature'], $body['webhook_secret']);

        return $body;
    }

    private function shouldLogQueueEvents(): bool
    {
        return (bool) config('logging.queue.enabled', true);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function logQueueEvent(string $level, string $message, array $context): void
    {
        if (! $this->shouldLogQueueEvents()) {
            return;
        }

        Log::{$level}($message, $this->sanitizeLogContext($context));
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function sanitizeLogContext(array $context): array
    {
        $sensitiveKeys = (array) config('logging.queue.sensitive_keys', [
            'token',
            'token_hash',
            'secret',
            'signature',
            'authorization',
            'webhook_secret',
            'raw_payload',
            'raw_response',
            'response_body',
            'payload',
            'device_key',
            'user_agent',
            'ip_address',
        ]);

        foreach ($sensitiveKeys as $key) {
            unset($context[(string) $key]);
        }

        return $context;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSafeContext(ChatWebhookDelivery $delivery): array
    {
        return [
            'job_class' => self::class,
            'queue' => $this->queue ?? 'webhooks',
            'job_delivery_id' => $this->deliveryId,
            'delivery_id' => $delivery->id,
            'delivery_uuid' => $delivery->delivery_uuid,
            'webhook_endpoint_id' => $delivery->webhook_endpoint_id,
            'event' => $delivery->event,
            'attempt' => method_exists($this, 'attempts') ? $this->attempts() : 1,
            'max_tries' => $this->tries,
        ];
    }

    private function durationMs(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }
}
