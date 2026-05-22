<?php

use App\Models\Conversation;
use App\Models\User;
use App\Services\Chat\ChatAccessService;
use App\Services\Chat\ChatPresenceService;
use App\Events\Chat\ChatUserJoinedConversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('test-broadcast', static fn () => true);

Broadcast::channel('system.notifications', static function (User $user): bool {
    return $user->hasPermission('notifications.view');
});

Broadcast::channel('activity.stream', static function (User $user): bool {
    return $user->hasPermission('activity.view');
});

Broadcast::channel('notifications.user.{userId}', static function (User $user, int $userId): bool {
    return $user->id === $userId;
});

Broadcast::channel('chat.conversation.{conversationId}', static function (User $user, int $conversationId): bool {
    $conversation = Conversation::query()->find($conversationId);
    if (! $conversation) {
        return false;
    }

    /** @var ChatAccessService $chatAccessService */
    $chatAccessService = app(ChatAccessService::class);

    return $chatAccessService->canViewMessages($user, $conversation);
});

Broadcast::channel('presence-online', static function (User $user): array {
    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});

Broadcast::channel('presence-dashboard', static function (User $user): array {
    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});

Broadcast::channel('presence-page.{page}', static function (User $user, string $page): array|bool {
    if (! preg_match('/^[a-z0-9._:-]{1,64}$/', $page)) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});

Broadcast::channel('presence-typing.{context}', static function (User $user, string $context): array|bool {
    if (! preg_match('/^[a-z0-9._:-]{1,64}$/', $context)) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});

Broadcast::channel('presence-chat.{conversationId}', static function (User $user, int $conversationId): array|bool {
    $conversation = Conversation::query()->find($conversationId);
    if (! $conversation) {
        return false;
    }

    /** @var ChatPresenceService $chatPresenceService */
    $chatPresenceService = app(ChatPresenceService::class);
    if (! $chatPresenceService->canJoinPresence($user, $conversation)) {
        return false;
    }

    $device = $chatPresenceService->markUserPresenceSeen($user, request()->input('device_key'));
    $payload = $chatPresenceService->buildPresencePayload($user, $conversation, $device);

    event(new ChatUserJoinedConversation(
        conversationId: $conversation->id,
        payload: [
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'name' => $user->name,
            'joined_at' => now()->toISOString(),
        ]
    ));

    return $payload;
});

// Backward compatibility alias for older frontend builds that still subscribe to chat.{conversationId}
Broadcast::channel('chat.{conversationId}', static function (User $user, int $conversationId): array|bool {
    $conversation = Conversation::query()->find($conversationId);
    if (! $conversation) {
        return false;
    }

    /** @var ChatPresenceService $chatPresenceService */
    $chatPresenceService = app(ChatPresenceService::class);
    if (! $chatPresenceService->canJoinPresence($user, $conversation)) {
        return false;
    }

    $device = $chatPresenceService->markUserPresenceSeen($user, request()->input('device_key'));
    $payload = $chatPresenceService->buildPresencePayload($user, $conversation, $device);

    event(new ChatUserJoinedConversation(
        conversationId: $conversation->id,
        payload: [
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'name' => $user->name,
            'joined_at' => now()->toISOString(),
        ]
    ));

    return $payload;
});
