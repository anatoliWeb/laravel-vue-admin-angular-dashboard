<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * User model.
 *
 * Represents authenticated system user.
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Hidden attributes.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casting.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Roles assigned to user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Direct permissions assigned to user.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Check if user has role.
     */
    public function hasRole(string $role): bool
    {
        return $this->roles()
            ->where('name', $role)
            ->exists();
    }

    /**
     * Check if user has any of roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()
            ->whereIn('name', $roles)
            ->exists();
    }

    /**
     * Check permission (direct OR via roles).
     */
    public function hasPermission(string $permission): bool
    {
        // Direct permission
        if ($this->permissions()
            ->where('name', $permission)
            ->exists()) {
            return true;
        }

        // Via roles
        return $this->roles()
            ->whereHas('permissions', function ($q) use ($permission) {
                $q->where('name', $permission);
            })
            ->exists();
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user is admin (via role).
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function deniedPermissions()
    {
        return $this->belongsToMany(Permission::class, 'user_denied_permissions');
    }
}
