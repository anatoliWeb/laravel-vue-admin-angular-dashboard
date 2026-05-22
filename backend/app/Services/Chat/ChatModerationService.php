<?php

namespace App\Services\Chat;

use App\Models\ChatModerationLog;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;

class ChatModerationService
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function logMessageCreated(User $actor, Message $message, array $metadata = []): ChatModerationLog
    {
        return $this->createLog(
            actor: $actor,
            action: 'message.created',
            conversation: $message->conversation,
            message: $message,
            targetUserId: $message->sender_id ? (int) $message->sender_id : null,
            metadata: $metadata,
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function logMessageUpdated(User $actor, Message $message, array $metadata = []): ChatModerationLog
    {
        return $this->createLog(
            actor: $actor,
            action: 'message.updated',
            conversation: $message->conversation,
            message: $message,
            targetUserId: $message->sender_id ? (int) $message->sender_id : null,
            metadata: $metadata,
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function logMessageDeleted(User $actor, Message $message, ?string $reason = null, array $metadata = []): ChatModerationLog
    {
        return $this->createLog(
            actor: $actor,
            action: 'message.deleted',
            conversation: $message->conversation,
            message: $message,
            targetUserId: $message->sender_id ? (int) $message->sender_id : null,
            reason: $reason,
            metadata: $metadata,
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function logMessageImported(User $actor, ?Message $message = null, array $metadata = []): ChatModerationLog
    {
        return $this->createLog(
            actor: $actor,
            action: 'message.imported',
            conversation: $message?->conversation,
            message: $message,
            targetUserId: $message?->sender_id ? (int) $message->sender_id : null,
            metadata: $metadata,
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function logExternalMessageCreated(?User $actor, Message $message, array $metadata = []): ChatModerationLog
    {
        return $this->createLog(
            actor: $actor,
            action: 'message.external_created',
            conversation: $message->conversation,
            message: $message,
            targetUserId: $message->sender_id ? (int) $message->sender_id : null,
            metadata: $metadata,
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function logConversationClosed(User $actor, Conversation $conversation, ?string $reason = null, array $metadata = []): ChatModerationLog
    {
        return $this->createLog(
            actor: $actor,
            action: 'conversation.closed',
            conversation: $conversation,
            reason: $reason,
            metadata: $metadata,
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function logConversationArchived(User $actor, Conversation $conversation, ?string $reason = null, array $metadata = []): ChatModerationLog
    {
        return $this->createLog(
            actor: $actor,
            action: 'conversation.archived',
            conversation: $conversation,
            reason: $reason,
            metadata: $metadata,
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function logParticipantRestricted(
        User $actor,
        ConversationParticipant $participant,
        string $action,
        ?string $reason = null,
        array $metadata = []
    ): ChatModerationLog {
        return $this->createLog(
            actor: $actor,
            action: $action,
            conversation: $participant->conversation,
            targetUserId: (int) $participant->user_id,
            reason: $reason,
            metadata: $metadata,
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function logAdminReplyCreated(User $actor, Message $message, array $metadata = []): ChatModerationLog
    {
        return $this->createLog(
            actor: $actor,
            action: 'message.admin_reply_created',
            conversation: $message->conversation,
            message: $message,
            targetUserId: $message->sender_id ? (int) $message->sender_id : null,
            metadata: $metadata,
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function sanitizeMetadata(array $metadata): array
    {
        $blockedKeys = [
            'token',
            'secret',
            'token_hash',
            'signing_secret',
            'signature',
            'raw_payload',
            'payload_raw',
            'raw_response',
            'response_raw',
            'authorization',
            'device_key',
            'user_agent',
            'ip_address',
            'disk',
            'path',
            'checksum',
            'webhook_secret',
        ];

        $safe = [];
        foreach ($metadata as $key => $value) {
            $isStringKey = is_string($key);

            if ($isStringKey && in_array(strtolower($key), $blockedKeys, true)) {
                continue;
            }

            if (is_array($value)) {
                $sanitizedNested = $this->sanitizeMetadata($value);
                if ($isStringKey) {
                    $safe[$key] = $sanitizedNested;
                } else {
                    $safe[] = $sanitizedNested;
                }
                continue;
            }

            if (is_scalar($value) || $value === null) {
                if ($isStringKey) {
                    $safe[$key] = $value;
                } else {
                    $safe[] = $value;
                }
            }
        }

        return $safe;
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private function createLog(
        ?User $actor,
        string $action,
        ?Conversation $conversation = null,
        ?Message $message = null,
        ?int $targetUserId = null,
        ?string $reason = null,
        array $metadata = []
    ): ChatModerationLog {
        $safeMetadata = $this->sanitizeMetadata($metadata);

        return ChatModerationLog::query()->create([
            'conversation_id' => $conversation?->id,
            'message_id' => $message?->id,
            'actor_id' => $actor?->id,
            'target_user_id' => $targetUserId,
            'action' => $action,
            'reason' => $reason,
            'old_values' => null,
            'new_values' => null,
            'metadata' => $safeMetadata === [] ? null : $safeMetadata,
        ]);
    }
}
