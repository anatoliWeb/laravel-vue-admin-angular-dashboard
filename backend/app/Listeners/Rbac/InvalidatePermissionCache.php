<?php

namespace App\Listeners\Rbac;

use App\Events\Rbac\RolePermissionsChanged;
use App\Services\Rbac\PermissionCacheService;

class InvalidatePermissionCache
{
    public function __construct(
        protected PermissionCacheService $permissionCacheService
    ) {
    }

    public function handle(RolePermissionsChanged $event): void
    {
        $this->permissionCacheService->forgetAll();
    }
}
