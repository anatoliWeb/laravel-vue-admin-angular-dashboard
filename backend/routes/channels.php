<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('test-broadcast', static fn () => true);

Broadcast::channel('system.notifications', static function (User $user): bool {
    return $user->hasPermission('notifications.view');
});
