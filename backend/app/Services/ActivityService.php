<?php

namespace App\Services;

use App\Jobs\LogActivityJob;
use App\Models\ActivityLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Activity service.
 *
 * Handles logging and retrieving activity data.
 */
class ActivityService
{
    /**
     * Queue new activity write operation.
     */
    public function log(?int $userId, string $action, ?string $description = null, array $meta = []): void
    {
        LogActivityJob::dispatch($userId, $action, $description, $meta);
    }

    /**
     * Persist activity record.
     *
     * WHY:
     * This method is used by queue jobs so write behavior remains centralized.
     */
    public function write(?int $userId, string $action, ?string $description = null, array $meta = []): void
    {
        try {
            ActivityLog::create([
                'user_id' => $userId,
                'action' => $action,
                'description' => $description,
                'meta' => $meta,
            ]);
        } catch (Throwable $exception) {
            Log::error('ActivityService::write failed', [
                'action' => $action,
                'user_id' => $userId,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Get recent activity for dashboard.
     *
     * @return Collection<int, ActivityLog>
     */
    public function getRecent(int $limit = 10): Collection
    {
        try {
            if (!Schema::hasTable('activity_logs')) {
                Log::warning('ActivityService::getRecent skipped: activity_logs table is not visible on current connection');
                return collect();
            }

            /** @var \App\Models\User|null $user */
            $user = auth()->user();

            $query = ActivityLog::with('user')->latest();

            // ============================================
            // SAFE ACCESS CONTROL (ROLE + PERMISSIONS)
            // ============================================

            if ($user) {

                // ----------------------------------------
                // 1. ROLE-BASED BASE ACCESS
                // ----------------------------------------

                if ($user->hasRole('admin')) {
                    // 👑 Admin → full access
                    // no restrictions

                } elseif ($user->hasRole('manager')) {
                    // 🧑‍💼 Manager → limited access (safe default)
                    $query->where('user_id', $user->id);

                } else {
                    // 👤 Regular user → only own logs
                    $query->where('user_id', $user->id);
                }

                // ----------------------------------------
                // 2. PERMISSION-BASED OVERRIDE (SAFE)
                // ----------------------------------------

                // IMPORTANT:
                // Do NOT assume permissions exist

                if (method_exists($user, 'hasPermissionTo')) {

                    // Example: allow full activity access
                    if ($user->hasPermissionTo('activity.view_all')) {
                        $query = ActivityLog::with('user')->latest();
                    }

                    // Example: allow viewing others in same role/group (future)
                    elseif ($user->hasPermissionTo('activity.view_team')) {
                        // Placeholder for future logic
                        // e.g. same company/team
                    }
                }
            }

            return $query->limit($limit)->get();

        } catch (Throwable $exception) {
            Log::error('ActivityService::getRecent failed', [
                'error' => $exception->getMessage(),
            ]);

            return collect();
        }
    }
}
