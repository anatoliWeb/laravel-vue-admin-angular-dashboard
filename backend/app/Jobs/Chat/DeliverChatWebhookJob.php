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
        $this->onQueue('realtime');
    }

    public function handle(
        ChatWebhookDeliveryService $deliveryService,
        ChatWebhookSigningService $signingService
    ): void {
        $delivery = ChatWebhookDelivery::query()
            ->with(['endpoint' => fn ($q) => $q->withTrashed()])
            ->find($this->deliveryId);

        if (! $delivery) {
            return;
        }

        if (in_array($delivery->status, ['sent', 'failed', 'cancelled'], true)) {
            return;
        }

        $endpoint = $delivery->endpoint;
        if (! $endpoint || $endpoint->trashed() || ! $endpoint->is_active || $endpoint->status !== 'active') {
            $deliveryService->markCancelled($delivery, 'Endpoint inactive or deleted');
            return;
        }

        $payloadJson = json_encode($delivery->payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payloadJson === false) {
            $deliveryService->markFailed($delivery, 'Invalid webhook payload');
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
                return;
            }

            $delivery = $deliveryService->markFailed($delivery, 'HTTP '.$response->status());
            $delivery->response_status = $response->status();
            $delivery->response_body = is_array($response->json())
                ? $this->sanitizeResponseBody($response->json())
                : null;
            $delivery->save();

            if ((int) $delivery->attempts >= (int) config('chat.webhooks.max_attempts', 5)) {
                return;
            }
            $deliveryService->scheduleRetry($delivery);
        } catch (Throwable $e) {
            $delivery = $deliveryService->markFailed($delivery, 'Webhook delivery exception');

            if ((int) $delivery->attempts >= (int) config('chat.webhooks.max_attempts', 5)) {
                return;
            }
            $deliveryService->scheduleRetry($delivery);
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
}
