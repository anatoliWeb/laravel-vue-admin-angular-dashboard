<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('test-broadcast', static fn () => true);

Broadcast::channel('system.notifications', static function (User $user): bool {
    return $user->hasPermission('notifications.view');
});

Broadcast::channel('activity.stream', static function (User $user): bool {
    return $user->hasPermission('activity.view');
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
