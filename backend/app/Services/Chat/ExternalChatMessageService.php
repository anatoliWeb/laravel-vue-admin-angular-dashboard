<?php

namespace App\Services\Chat;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class ExternalChatMessageService
{
    public function __construct(
        protected ChatMessageService $chatMessageService,
        protected ExternalMessageMappingService $mappingService,
        protected ChatAccessService $accessService,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{message: Message, idempotent: bool}
     */
    public function sendExternalMessage(User $actor, array $payload): array
    {
        if (! $actor->hasAnyPermission(['chat.external_api.send', 'chat.external_api.manage', 'chat.admin.moderate'])) {
            throw new AuthorizationException('You are not allowed to send external chat messages.');
        }

        $conversation = Conversation::query()->findOrFail((int) $payload['conversation_id']);
        if (! in_array((string) $conversation->type, ['external', 'support', 'system'], true)) {
            throw ValidationException::withMessages([
                'conversation_id' => ['External API message sending is allowed only for external/support/system conversations.'],
            ]);
        }

        if (! $this->accessService->canViewConversation($actor, $conversation)) {
            throw new AuthorizationException('You are not allowed to access this conversation.');
        }

        $provider = trim((string) $payload['external_provider']);
        $externalMessageId = trim((string) $payload['external_message_id']);
        $existingMapping = $this->mappingService->findByExternalId($provider, $externalMessageId);
        if ($existingMapping) {
            if ((int) $existingMapping->conversation_id !== (int) $conversation->id) {
                throw ValidationException::withMessages([
                    'external_message_id' => ['External message id is already mapped to another conversation.'],
                ]);
            }

            $existingMessage = $existingMapping->message;
            if (! $existingMessage) {
                throw ValidationException::withMessages([
                    'external_message_id' => ['External message mapping is invalid.'],
                ]);
            }

            return [
                'message' => $existingMessage,
                'idempotent' => true,
            ];
        }

        $type = (string) ($payload['type'] ?? 'text');
        if (! in_array($type, ['text', 'system'], true)) {
            throw ValidationException::withMessages([
                'type' => ['Unsupported external message type.'],
            ]);
        }

        $message = $this->chatMessageService->sendMessage($actor, $conversation, [
            'body' => (string) $payload['body'],
            'type' => $type,
        ]);

        $safeMetadata = $this->sanitizeMetadata((array) ($payload['metadata'] ?? []));
        $message->external_id = $externalMessageId;
        $message->metadata = array_filter([
            'source' => 'external_api',
            'direction' => 'external_in',
            'provider' => $provider,
            'idempotency_key' => $payload['idempotency_key'] ?? null,
            'external_metadata' => $safeMetadata !== [] ? $safeMetadata : null,
        ], static fn ($value) => $value !== null);
        if (! empty($payload['sent_at'])) {
            $message->sent_at = $payload['sent_at'];
        }
        $message->save();

        $this->mappingService->mapExternalMessage(
            conversation: $conversation,
            message: $message,
            provider: $provider,
            externalMessageId: $externalMessageId,
            payload: [
                'source' => 'external_api',
                'direction' => 'external_in',
                'idempotency_key' => $payload['idempotency_key'] ?? null,
            ]
        );

        return [
            'message' => $message->fresh(),
            'idempotent' => false,
        ];
    }

    /**
     * @param array<string, mixed> $metadata
     * @return array<string, mixed>
     */
    private function sanitizeMetadata(array $metadata): array
    {
        $forbiddenKeys = [
            'user_agent',
            'ip_address',
            'token',
            'secret',
            'password',
            'authorization',
        ];

        $safe = [];
        foreach ($metadata as $key => $value) {
            $normalizedKey = strtolower((string) $key);
            if (in_array($normalizedKey, $forbiddenKeys, true)) {
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $safe[(string) $key] = $value;
            }
        }

        return $safe;
    }
}
