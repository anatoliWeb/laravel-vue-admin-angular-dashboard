<?php

namespace App\Services;

use App\DTO\StatsDTO;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\ActivityLog;
use Illuminate\Support\Collection;
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
        $stats = new StatsDTO(
            users: User::count(),
            roles: Role::count(),
            permissions: Permission::count(),
            activityLogs: ActivityLog::count(),
            admins: User::whereHas('roles', fn($q) =>
                $q->where('name', 'admin')
            )->count(),
            managers: User::whereHas('roles', fn($q) =>
                $q->where('name', 'manager')
            )->count(),
            tokens: PersonalAccessToken::count(),
            usersWithDirectPermissions: User::whereHas('permissions')->count(),
            recentActivity: $this->normalizeRecentActivity(
                $this->activityService->getRecent()
            ),
        );

        return [
            'data' => $stats->toArray(),
        ];
    }

    /**
     * Normalize recent activity payload to plain array for DTO typing.
     *
     * @param mixed $value
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeRecentActivity(mixed $value): array
    {
        if ($value instanceof Collection) {
            /** @var array<int, array<string, mixed>> $normalized */
            $normalized = $value->values()->all();
            return $normalized;
        }

        if (is_array($value)) {
            return array_values($value);
        }

        return [];
    }


}
