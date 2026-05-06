<?php

namespace App\Services;

use App\Models\User;
use App\Models\Permission;
use App\DTO\UserDTO;
use Illuminate\Support\Facades\Hash;

/**
 * User service.
 *
 * WHY:
 * Encapsulates all user-related business logic in one place,
 * keeping controllers thin and focused on request/response handling.
 *
 * Provides a single source of truth for:
 * - user CRUD operations
 * - RBAC synchronization (roles + permissions)
 * - data transformation (DTO)
 */
class UserService
{
    /**
     * Convert User model to stable API DTO shape.
     *
     * WHY:
     * Frontend should receive one consistent contract across
     * list/show/create/update responses.
     */
    protected function toDto(User $user): UserDTO
    {
        return new UserDTO(
            $user->id,
            $user->name,
            $user->email,
            $user->roles->pluck('name')->values()->all(),
            $user->permissions->pluck('name')->values()->all(),
            $user->deniedPermissions->pluck('name')->values()->all(),
        );
    }

    /**
     * Get all users as DTO collection.
     *
     * WHY:
     * DTO isolates API output from internal model structure
     * and prevents accidental data exposure (e.g. passwords, hidden fields).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getUsersForApi(): array
    {
        return User::with(['roles:id,name', 'permissions:id,name', 'deniedPermissions:id,name'])
            ->get()
            ->map(fn (User $user) => $this->toDto($user))
            ->values()
            ->all();
    }

    /**
     * Backward-compatible alias for existing calls.
     */
    public function getUsers(): array
    {
        return $this->getUsersForApi();
    }

    /**
     * Get single user as DTO.
     *
     * WHY:
     * Keeps API response consistent with list endpoint
     * and avoids exposing raw Eloquent models.
     */
    public function getUser(int $id): UserDTO
    {
        $user = User::with(['roles:id,name', 'permissions:id,name', 'deniedPermissions:id,name'])->findOrFail($id);
        return $this->toDto($user);
    }

    /**
     * Get users list for Blade admin pages.
     *
     * WHY:
     * Consistent naming improves readability and maintainability across the project.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getUsersForAdmin(): array
    {
        return $this->getUsersForApi();
    }

    /**
     * Get raw user model with relations.
     *
     * WHY:
     * Used internally (e.g. admin forms) where full model access is required.
     * Loads only necessary fields to optimize query performance.
     */
    public function getById(int $id): array
    {
        $user = User::with(['roles:id,name', 'permissions:id,name', 'deniedPermissions:id,name'])->findOrFail($id);
        return $this->toDto($user)->toArray();
    }

    /**
     * Create new user.
     *
     * WHY:
     * Handles:
     * - secure password hashing
     * - role assignment (RBAC)
     * - direct permission assignment
     *
     * Keeps all user creation logic centralized.
     */
    public function create(array $data): array
    {
        // WHY:
        // Password must always be hashed before storing
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // WHY:
        // Sync roles (many-to-many)
        // Using sync ensures full replacement (no duplicates)
        $user->roles()->sync($data['roles'] ?? []);

        // WHY:
        // Permissions are passed as names from frontend
        // Convert them to IDs before syncing to maintain DB integrity
        $user->permissions()->sync(
            Permission::whereIn('name', $data['permissions'] ?? [])->pluck('id')
        );

        $user->deniedPermissions()->sync(
            Permission::whereIn('name', $data['denied_permissions'] ?? [])->pluck('id')
        );

        // WHY:
        // Reload relations to return fresh state to API
        return $this->toDto(
            $user->load('roles:id,name', 'permissions:id,name', 'deniedPermissions:id,name')
        )->toArray();
    }

    /**
     * Update existing user.
     *
     * WHY:
     * Supports partial updates while preserving security:
     * - password updated only if provided
     * - roles and permissions are fully synchronized
     */
    public function update(int $id, array $data): array
    {
        $user = User::findOrFail($id);
        $isSelfUpdate = auth()->id() === $user->id;

        // WHY:
        // Build update payload explicitly to avoid mass-assignment issues
        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        // WHY:
        // Only update password if provided (nullable in request)
        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);

        if (!$isSelfUpdate) {
            // WHY:
            // Sync roles to reflect current state exactly
            $user->roles()->sync($data['roles'] ?? []);

            // WHY:
            // Convert permission names to IDs and sync
            $user->permissions()->sync(
                Permission::whereIn('name', $data['permissions'] ?? [])->pluck('id')
            );

            $user->deniedPermissions()->sync(
                Permission::whereIn('name', $data['denied_permissions'] ?? [])->pluck('id')
            );
        }
        // WHY:
        // Security rule: user must not be able to remove own critical permissions.
        // Even if frontend is bypassed, backend ignores self-role/self-permission edits.

        return $this->toDto(
            $user->load('roles:id,name', 'permissions:id,name', 'deniedPermissions:id,name')
        )->toArray();
    }

    /**
     * Delete user.
     *
     * WHY:
     * Ensures relations are cleaned up before deletion
     * to maintain database integrity.
     */
    public function delete(int $id): void
    {
        $user = User::findOrFail($id);

        // WHY:
        // Detach roles before delete to avoid orphaned pivot data
        $user->roles()->detach();

        $user->delete();
    }
}
