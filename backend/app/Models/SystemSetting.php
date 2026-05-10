<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SystemSetting model for hierarchical configuration records.
 *
 * WHY THIS MODEL EXISTS:
 * Settings are persisted as data, not hardcoded constants, so platform behavior
 * can be changed safely per scope (global/role/permission/user) without deploys.
 */
class SystemSetting extends Model
{
    protected $fillable = [
        'scope_user_id',
        'scope_role_id',
        'scope_permission_id',
        'key',
        'label',
        'group',
        'description',
        'type',
        'value',
        'default_value',
        'is_frontend',
        'is_backend',
        'priority',
        'is_active',
        'is_system',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_frontend' => 'boolean',
        'is_backend' => 'boolean',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'priority' => 'integer',
    ];

    public function scopeUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scope_user_id');
    }

    public function scopeRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'scope_role_id');
    }

    public function scopePermission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'scope_permission_id');
    }
}

