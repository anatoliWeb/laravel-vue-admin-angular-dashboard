<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\ActivityLog;
use Laravel\Sanctum\PersonalAccessToken;
use App\Services\ActivityService;

/**
 * Stats service.
 *
 * Provides dashboard metrics.
 */
class StatsService
{
    protected ActivityService $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * Get dashboard statistics.
     *
     * @return array<string, int>
     */
    public function getStats(): array
    {
        return [
            'data' => [
                'users' => User::count(),
                'roles' => Role::count(),
                'permissions' => Permission::count(),
                'activity_logs' => ActivityLog::count(),
                'admins' => User::whereHas('roles', fn($q) =>
                    $q->where('name', 'admin')
                    )->count(),
                'managers' => User::whereHas('roles', fn($q) =>
                    $q->where('name', 'manager')
                    )->count(),
                'tokens' => PersonalAccessToken::count(),
                'users_with_direct_permissions' => User::whereHas('permissions')->count(),
                'recent_activity' => $this->activityService->getRecent(),
            ]
        ];
    }


}
