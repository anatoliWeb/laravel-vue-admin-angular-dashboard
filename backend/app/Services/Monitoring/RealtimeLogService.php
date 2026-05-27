<?php

namespace App\Services\Monitoring;

use Illuminate\Support\Facades\Log;

class RealtimeLogService
{
    /**
     * @param array<string, mixed> $context
     */
    public function logChannelDenied(array $context): void
    {
        if (! $this->isEnabled() || ! $this->logChannelAuthFailures()) {
            return;
        }

        Log::warning('realtime.channel.auth.denied', $this->sanitizeContext($context + [
            'status' => 'denied',
        ]));
    }

    /**
     * @param array<string, mixed> $context
     */
    public function logBroadcastFailed(array $context): void
    {
        if (! $this->isEnabled() || ! $this->logBroadcastFailures()) {
            return;
        }

        Log::error('realtime.broadcast.failed', $this->sanitizeContext($context + [
            'status' => 'failed',
        ]));
    }

    public function isEnabled(): bool
    {
        return (bool) config('logging.realtime.enabled', true);
    }

    public function logChannelAuthFailures(): bool
    {
        return (bool) config('logging.realtime.channel_auth_failures', true);
    }

    public function logBroadcastFailures(): bool
    {
        return (bool) config('logging.realtime.broadcast_failures', true);
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function sanitizeContext(array $context): array
    {
        $sensitiveKeys = (array) config('logging.realtime.sensitive_keys', [
            'token',
            'token_hash',
            'authorization',
            'cookie',
            'cookies',
            'signature',
            'secret',
            'webhook_secret',
            'raw_payload',
            'raw_response',
            'payload',
            'body',
            'message_body',
            'device_key',
            'user_agent',
            'ip_address',
            'email',
        ]);

        foreach ($sensitiveKeys as $key) {
            unset($context[(string) $key]);
        }

        return $context;
    }
}

