<?php

namespace App\Services\Chat;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\MessageDelivery;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ChatMessageService
{
    public function __construct(
        protected ChatAccessService $accessService
    ) {
    }

    public function sendMessage(User $sender, Conversation $conversation, array $payload): Message
    {
        if (! $this->accessService->canSendMessage($sender, $conversation)) {
            throw new AuthorizationException('You are not allowed to send messages in this conversation.');
        }

        if ($conversation->status !== 'active') {
            throw ValidationException::withMessages([
                'conversation' => ['Messages can only be sent to active conversations.'],
            ]);
        }

        $type = (string) ($payload['type'] ?? 'text');
        if (! in_array($type, ['text'], true)) {
            throw ValidationException::withMessages([
                'type' => ['Only text message type is allowed in this phase.'],
            ]);
        }

        $body = trim((string) ($payload['body'] ?? ''));
        if ($body === '') {
            throw ValidationException::withMessages([
                'body' => ['Message body is required.'],
            ]);
        }

        $senderType = $sender->hasAnyPermission(['chat.admin.reply', 'chat.admin.moderate']) ? 'admin' : 'user';

        return DB::transaction(function () use ($sender, $conversation, $body, $type, $senderType): Message {
            $message = Message::query()->create([
                'uuid' => (string) Str::uuid(),
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'sender_type' => $senderType,
                'external_id' => null,
                'reply_to_message_id' => null,
                'type' => $type,
                'body' => $body,
                'status' => 'sent',
                'is_imported' => false,
                'imported_from_conversation_id' => null,
                'imported_from_message_id' => null,
                'sent_at' => now(),
                'delivered_at' => null,
                'read_at' => null,
                'edited_at' => null,
                'deleted_at' => null,
                'metadata' => null,
            ]);

            $conversation->last_message_id = $message->id;
            $conversation->last_message_at = $message->created_at;
            $conversation->save();

            $this->createDeliveriesForActiveParticipants($conversation, $message, $sender);

            return $message->fresh();
        });
    }

    public function editMessage(User $actor, Message $message, array $payload): Message
    {
        if ($message->status === 'deleted' || $message->deleted_at !== null) {
            throw ValidationException::withMessages([
                'message' => ['Deleted message cannot be edited.'],
            ]);
        }

        if ($message->is_imported) {
            throw ValidationException::withMessages([
                'message' => ['Imported message cannot be edited.'],
            ]);
        }

        $conversation = $message->conversation;
        if (! $conversation) {
            throw ValidationException::withMessages([
                'message' => ['Message conversation is invalid.'],
            ]);
        }

        $isOwner = (int) $message->sender_id === (int) $actor->id;
        $isModerator = $actor->hasAnyPermission(['chat.admin.moderate']);
        if (! $isOwner && ! $isModerator) {
            throw new AuthorizationException('You are not allowed to edit this message.');
        }

        $body = trim((string) ($payload['body'] ?? ''));
        if ($body === '') {
            throw ValidationException::withMessages([
                'body' => ['Message body is required.'],
            ]);
        }

        $message->body = $body;
        $message->edited_at = now();
        $message->save();

        return $message->fresh();
    }

    public function deleteMessage(User $actor, Message $message): Message
    {
        if ($message->status === 'deleted' || $message->deleted_at !== null) {
            return $message;
        }

        $conversation = $message->conversation;
        if (! $conversation) {
            throw ValidationException::withMessages([
                'message' => ['Message conversation is invalid.'],
            ]);
        }

        $isOwner = (int) $message->sender_id === (int) $actor->id;
        $isModerator = $actor->hasAnyPermission(['chat.admin.delete_messages', 'chat.admin.moderate']);
        if (! $isOwner && ! $isModerator) {
            throw new AuthorizationException('You are not allowed to delete this message.');
        }

        return DB::transaction(function () use ($message, $conversation): Message {
            $message->status = 'deleted';
            $message->deleted_at = now();
            // WHY: scrub message body on soft delete to reduce sensitive text exposure.
            $message->body = null;
            $message->save();

            if ((int) $conversation->last_message_id === (int) $message->id) {
                $previousVisible = Message::query()
                    ->where('conversation_id', $conversation->id)
                    ->where('id', '!=', $message->id)
                    ->whereNull('deleted_at')
                    ->where('status', '!=', 'deleted')
                    ->orderByDesc('id')
                    ->first();

                $conversation->last_message_id = $previousVisible?->id;
                $conversation->last_message_at = $previousVisible?->created_at;
                $conversation->save();
            }

            return $message->fresh();
        });
    }

    private function createDeliveriesForActiveParticipants(Conversation $conversation, Message $message, User $sender): void
    {
        $participantIds = ConversationParticipant::query()
            ->where('conversation_id', $conversation->id)
            ->where('status', 'active')
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $userId) => $userId !== (int) $sender->id)
            ->values();

        foreach ($participantIds as $recipientId) {
            MessageDelivery::query()->updateOrCreate(
                [
                    'message_id' => $message->id,
                    'user_id' => $recipientId,
                ],
                [
                    'conversation_id' => $conversation->id,
                    'external_recipient_id' => null,
                    'recipient_type' => 'user',
                    'status' => 'pending',
                    'delivered_at' => null,
                    'failed_at' => null,
                    'failure_reason' => null,
                    'metadata' => null,
                ]
            );
        }
    }
}

