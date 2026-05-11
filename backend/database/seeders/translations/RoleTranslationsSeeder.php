<?php

namespace Database\Seeders\translations;

/**
 * Seeds dynamic role translations.
 *
 * WHY:
 * Roles are business entities stored in database and therefore
 * require runtime localization support.
 *
 * Static translation files are intentionally NOT used for roles because:
 * - roles may be created dynamically
 * - roles may be tenant-specific
 * - admins may rename roles
 * - localization may change at runtime
 */
class RoleTranslationsSeeder extends BaseTranslationsSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedTranslations([

            /*
            |--------------------------------------------------------------------------
            | Administrator
            |--------------------------------------------------------------------------
            */

            [
                'locale' => 'en',
                'group' => 'roles',
                'key' => 'role.admin',
                'value' => 'Administrator',
                'description' => 'System administrator role label.',
            ],

            [
                'locale' => 'uk',
                'group' => 'roles',
                'key' => 'role.admin',
                'value' => 'Адміністратор',
                'description' => 'Назва ролі адміністратора.',
            ],

            [
                'locale' => 'de',
                'group' => 'roles',
                'key' => 'role.admin',
                'value' => 'Administrator',
                'description' => 'Administratorrollenbezeichnung.',
            ],

            /*
            |--------------------------------------------------------------------------
            | User
            |--------------------------------------------------------------------------
            */

            [
                'locale' => 'en',
                'group' => 'roles',
                'key' => 'role.user',
                'value' => 'User',
            ],

            [
                'locale' => 'uk',
                'group' => 'roles',
                'key' => 'role.user',
                'value' => 'Користувач',
            ],

            [
                'locale' => 'de',
                'group' => 'roles',
                'key' => 'role.user',
                'value' => 'Benutzer',
            ],

            /*
            |--------------------------------------------------------------------------
            | Manager
            |--------------------------------------------------------------------------
            */

            [
                'locale' => 'en',
                'group' => 'roles',
                'key' => 'role.manager',
                'value' => 'Manager',
            ],

            [
                'locale' => 'uk',
                'group' => 'roles',
                'key' => 'role.manager',
                'value' => 'Менеджер',
            ],

            [
                'locale' => 'de',
                'group' => 'roles',
                'key' => 'role.manager',
                'value' => 'Manager',
            ],

            /*
            |--------------------------------------------------------------------------
            | Moderator
            |--------------------------------------------------------------------------
            */

            [
                'locale' => 'en',
                'group' => 'roles',
                'key' => 'role.moderator',
                'value' => 'Moderator',
            ],

            [
                'locale' => 'uk',
                'group' => 'roles',
                'key' => 'role.moderator',
                'value' => 'Модератор',
            ],

            [
                'locale' => 'de',
                'group' => 'roles',
                'key' => 'role.moderator',
                'value' => 'Moderator',
            ],

            /*
            |--------------------------------------------------------------------------
            | Support
            |--------------------------------------------------------------------------
            */

            [
                'locale' => 'en',
                'group' => 'roles',
                'key' => 'role.support',
                'value' => 'Support',
            ],

            [
                'locale' => 'uk',
                'group' => 'roles',
                'key' => 'role.support',
                'value' => 'Підтримка',
            ],

            [
                'locale' => 'de',
                'group' => 'roles',
                'key' => 'role.support',
                'value' => 'Support',
            ],
        ]);
    }
}
