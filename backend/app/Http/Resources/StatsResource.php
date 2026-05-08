<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Stats API resource.
 *
 * WHY THIS RESOURCE EXISTS:
 * Dashboard widgets require a predictable metrics schema independent from
 * internal service implementation details.
 *
 * WHY NOT RETURN RAW SERVICE/QUERY STRUCTURES:
 * Raw structures can drift over time and make frontend widgets fragile.
 *
 * WHAT THIS RESOURCE CONTROLS:
 * It defines exactly which metrics are exposed and keeps names stable for UI
 * cards and future versioning.
 */
class StatsResource extends JsonResource
{
    /**
     * Transform stats payload into stable API structure.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $stats = data_get($this->resource, 'data', []);

        return [
            'users' => data_get($stats, 'users', 0),
            'roles' => data_get($stats, 'roles', 0),
            'permissions' => data_get($stats, 'permissions', 0),
            'activity_logs' => data_get($stats, 'activity_logs', 0),
            'admins' => data_get($stats, 'admins', 0),
            'managers' => data_get($stats, 'managers', 0),
            'tokens' => data_get($stats, 'tokens', 0),
            'users_with_direct_permissions' => data_get($stats, 'users_with_direct_permissions', 0),
            'recent_activity' => data_get($stats, 'recent_activity', []),
        ];
    }
}
