<?php

namespace App\Listeners\Rbac;

use App\Events\Rbac\PermissionChanged;
use App\Events\Rbac\RolePermissionsChanged;
use App\Services\Rbac\PermissionCacheService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class InvalidatePermissionCache implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        protected PermissionCacheService $permissionCacheService
    ) {
    }

    public function handle(RolePermissionsChanged|PermissionChanged $event): void
    {
        $this->permissionCacheService->forgetAll();
    }
}
