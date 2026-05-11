<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Dynamic runtime translation entity.
 *
 * WHY:
 * Stores business/runtime translations that should not live
 * inside static language files.
 *
 * Examples:
 * - roles
 * - permissions
 * - settings
 * - CMS labels
 * - dynamic menus
 */
class SystemTranslation extends Model
{
    protected $fillable = [
        'locale',
        'group',
        'key',
        'value',
        'source',
        'description',
        'is_frontend',
        'is_backend',
        'is_system',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_frontend' => 'boolean',
        'is_backend' => 'boolean',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];
}
