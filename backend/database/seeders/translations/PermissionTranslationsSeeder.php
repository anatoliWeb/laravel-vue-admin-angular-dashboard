<?php

namespace Database\Seeders\translations;

/**
 * Seeds dynamic permission translations.
 *
 * WHY:
 * Permissions are database-driven RBAC entities and therefore require
 * runtime localization support.
 *
 * This architecture prepares the platform for:
 * - admin-managed permissions
 * - enterprise RBAC
 * - tenant-specific permissions
 * - runtime authorization UI generation
 *
 * IMPORTANT:
 * Permission keys themselves should remain stable and machine-readable.
 *
 * Translation values are ONLY human-facing labels.
 */
class PermissionTranslationsSeeder extends BaseTranslationsSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedTranslations([

            /*
            |--------------------------------------------------------------------------
            | Users
            |--------------------------------------------------------------------------
            */

            [
                'locale' => 'en',
                'group' => 'permissions',
                'key' => 'permission.users.view',
                'value' => 'View Users',
            ],

            [
                'locale' => 'uk',
                'group' => 'permissions',
                'key' => 'permission.users.view',
                'value' => 'Перегляд користувачів',
            ],

            [
                'locale' => 'de',
                'group' => 'permissions',
                'key' => 'permission.users.view',
                'value' => 'Benutzer anzeigen',
            ],

            [
                'locale' => 'en',
                'group' => 'permissions',
                'key' => 'permission.users.create',
                'value' => 'Create Users',
            ],

            [
                'locale' => 'uk',
                'group' => 'permissions',
                'key' => 'permission.users.create',
                'value' => 'Створення користувачів',
            ],

            [
                'locale' => 'de',
                'group' => 'permissions',
                'key' => 'permission.users.create',
                'value' => 'Benutzer erstellen',
            ],

            [
                'locale' => 'en',
                'group' => 'permissions',
                'key' => 'permission.users.edit',
                'value' => 'Edit Users',
            ],

            [
                'locale' => 'uk',
                'group' => 'permissions',
                'key' => 'permission.users.edit',
                'value' => 'Редагування користувачів',
            ],

            [
                'locale' => 'de',
                'group' => 'permissions',
                'key' => 'permission.users.edit',
                'value' => 'Benutzer bearbeiten',
            ],

            [
                'locale' => 'en',
                'group' => 'permissions',
                'key' => 'permission.users.delete',
                'value' => 'Delete Users',
            ],

            [
                'locale' => 'uk',
                'group' => 'permissions',
                'key' => 'permission.users.delete',
                'value' => 'Видалення користувачів',
            ],

            [
                'locale' => 'de',
                'group' => 'permissions',
                'key' => 'permission.users.delete',
                'value' => 'Benutzer löschen',
            ],

            /*
            |--------------------------------------------------------------------------
            | Roles
            |--------------------------------------------------------------------------
            */

            [
                'locale' => 'en',
                'group' => 'permissions',
                'key' => 'permission.roles.view',
                'value' => 'View Roles',
            ],

            [
                'locale' => 'uk',
                'group' => 'permissions',
                'key' => 'permission.roles.view',
                'value' => 'Перегляд ролей',
            ],

            [
                'locale' => 'de',
                'group' => 'permissions',
                'key' => 'permission.roles.view',
                'value' => 'Rollen anzeigen',
            ],

            [
                'locale' => 'en',
                'group' => 'permissions',
                'key' => 'permission.roles.create',
                'value' => 'Create Roles',
            ],

            [
                'locale' => 'uk',
                'group' => 'permissions',
                'key' => 'permission.roles.create',
                'value' => 'Створення ролей',
            ],

            [
                'locale' => 'de',
                'group' => 'permissions',
                'key' => 'permission.roles.create',
                'value' => 'Rollen erstellen',
            ],

            [
                'locale' => 'en',
                'group' => 'permissions',
                'key' => 'permission.roles.edit',
                'value' => 'Edit Roles',
            ],

            [
                'locale' => 'uk',
                'group' => 'permissions',
                'key' => 'permission.roles.edit',
                'value' => 'Редагування ролей',
            ],

            [
                'locale' => 'de',
                'group' => 'permissions',
                'key' => 'permission.roles.edit',
                'value' => 'Rollen bearbeiten',
            ],

            [
                'locale' => 'en',
                'group' => 'permissions',
                'key' => 'permission.roles.delete',
                'value' => 'Delete Roles',
            ],

            [
                'locale' => 'uk',
                'group' => 'permissions',
                'key' => 'permission.roles.delete',
                'value' => 'Видалення ролей',
            ],

            [
                'locale' => 'de',
                'group' => 'permissions',
                'key' => 'permission.roles.delete',
                'value' => 'Rollen löschen',
            ],

            /*
            |--------------------------------------------------------------------------
            | Permissions
            |--------------------------------------------------------------------------
            */

            [
                'locale' => 'en',
                'group' => 'permissions',
                'key' => 'permission.permissions.view',
                'value' => 'View Permissions',
            ],

            [
                'locale' => 'uk',
                'group' => 'permissions',
                'key' => 'permission.permissions.view',
                'value' => 'Перегляд дозволів',
            ],

            [
                'locale' => 'de',
                'group' => 'permissions',
                'key' => 'permission.permissions.view',
                'value' => 'Berechtigungen anzeigen',
            ],

            [
                'locale' => 'en',
                'group' => 'permissions',
                'key' => 'permission.permissions.edit',
                'value' => 'Edit Permissions',
            ],

            [
                'locale' => 'uk',
                'group' => 'permissions',
                'key' => 'permission.permissions.edit',
                'value' => 'Редагування дозволів',
            ],

            [
                'locale' => 'de',
                'group' => 'permissions',
                'key' => 'permission.permissions.edit',
                'value' => 'Berechtigungen bearbeiten',
            ],

            /*
            |--------------------------------------------------------------------------
            | Settings
            |--------------------------------------------------------------------------
            */

            [
                'locale' => 'en',
                'group' => 'permissions',
                'key' => 'permission.settings.view',
                'value' => 'View Settings',
            ],

            [
                'locale' => 'uk',
                'group' => 'permissions',
                'key' => 'permission.settings.view',
                'value' => 'Перегляд налаштувань',
            ],

            [
                'locale' => 'de',
                'group' => 'permissions',
                'key' => 'permission.settings.view',
                'value' => 'Einstellungen anzeigen',
            ],

            [
                'locale' => 'en',
                'group' => 'permissions',
                'key' => 'permission.settings.update',
                'value' => 'Update Settings',
            ],

            [
                'locale' => 'uk',
                'group' => 'permissions',
                'key' => 'permission.settings.update',
                'value' => 'Оновлення налаштувань',
            ],

            [
                'locale' => 'de',
                'group' => 'permissions',
                'key' => 'permission.settings.update',
                'value' => 'Einstellungen aktualisieren',
            ],

            /*
            |--------------------------------------------------------------------------
            | Tokens
            |--------------------------------------------------------------------------
            */

            [
                'locale' => 'en',
                'group' => 'permissions',
                'key' => 'permission.tokens.manage',
                'value' => 'Manage Tokens',
            ],

            [
                'locale' => 'uk',
                'group' => 'permissions',
                'key' => 'permission.tokens.manage',
                'value' => 'Керування токенами',
            ],

            [
                'locale' => 'de',
                'group' => 'permissions',
                'key' => 'permission.tokens.manage',
                'value' => 'Token verwalten',
            ],
        ]);
    }
}
