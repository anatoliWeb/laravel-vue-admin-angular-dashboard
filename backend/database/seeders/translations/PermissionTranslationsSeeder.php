<?php

namespace Database\Seeders\translations;

/**
 * Seeds permission localization keys.
 *
 * TECHNICAL IDENTIFIER POLICY:
 * `permissions.name` remains immutable (e.g. users.create).
 * This seeder adds multilingual labels/descriptions only.
 */
class PermissionTranslationsSeeder extends BaseTranslationsSeeder
{
    public function run(): void
    {
        $labels = [
            'access_admin' => ['en' => 'Access Admin Panel', 'uk' => 'Доступ до адмін-панелі', 'de' => 'Zugriff auf Admin-Panel'],
            'users.view' => ['en' => 'View users', 'uk' => 'Перегляд користувачів', 'de' => 'Benutzer anzeigen'],
            'users.create' => ['en' => 'Create users', 'uk' => 'Створення користувачів', 'de' => 'Benutzer erstellen'],
            'users.edit' => ['en' => 'Edit users', 'uk' => 'Редагування користувачів', 'de' => 'Benutzer bearbeiten'],
            'users.delete' => ['en' => 'Delete users', 'uk' => 'Видалення користувачів', 'de' => 'Benutzer löschen'],
            'roles.view' => ['en' => 'View roles', 'uk' => 'Перегляд ролей', 'de' => 'Rollen anzeigen'],
            'roles.create' => ['en' => 'Create roles', 'uk' => 'Створення ролей', 'de' => 'Rollen erstellen'],
            'roles.edit' => ['en' => 'Edit roles', 'uk' => 'Редагування ролей', 'de' => 'Rollen bearbeiten'],
            'roles.delete' => ['en' => 'Delete roles', 'uk' => 'Видалення ролей', 'de' => 'Rollen löschen'],
            'permissions.view' => ['en' => 'View permissions', 'uk' => 'Перегляд дозволів', 'de' => 'Berechtigungen anzeigen'],
            'permissions.create' => ['en' => 'Create permissions', 'uk' => 'Створення дозволів', 'de' => 'Berechtigungen erstellen'],
            'permissions.edit' => ['en' => 'Edit permissions', 'uk' => 'Редагування дозволів', 'de' => 'Berechtigungen bearbeiten'],
            'permissions.delete' => ['en' => 'Delete permissions', 'uk' => 'Видалення дозволів', 'de' => 'Berechtigungen löschen'],
            'tokens.view' => ['en' => 'View tokens', 'uk' => 'Перегляд токенів', 'de' => 'Token anzeigen'],
            'tokens.create' => ['en' => 'Create tokens', 'uk' => 'Створення токенів', 'de' => 'Token erstellen'],
            'tokens.delete' => ['en' => 'Delete tokens', 'uk' => 'Видалення токенів', 'de' => 'Token löschen'],
        ];

        $descriptions = [
            'access_admin' => [
                'en' => 'Allows opening the administrative application shell.',
                'uk' => 'Дозволяє відкривати адміністративну оболонку застосунку.',
                'de' => 'Erlaubt das Öffnen der administrativen Anwendungsshell.',
            ],
            'users.view' => [
                'en' => 'Allows viewing user records and profile metadata.',
                'uk' => 'Дозволяє переглядати записи користувачів і метадані профілю.',
                'de' => 'Erlaubt das Anzeigen von Benutzerdatensätzen und Profilmetadaten.',
            ],
            'users.create' => [
                'en' => 'Allows creating new user accounts.',
                'uk' => 'Дозволяє створювати нові облікові записи користувачів.',
                'de' => 'Erlaubt das Erstellen neuer Benutzerkonten.',
            ],
            'users.edit' => [
                'en' => 'Allows updating existing user records.',
                'uk' => 'Дозволяє оновлювати існуючі записи користувачів.',
                'de' => 'Erlaubt das Aktualisieren bestehender Benutzerdatensätze.',
            ],
            'users.delete' => [
                'en' => 'Allows deleting user accounts from the platform.',
                'uk' => 'Дозволяє видаляти облікові записи користувачів з платформи.',
                'de' => 'Erlaubt das Löschen von Benutzerkonten von der Plattform.',
            ],
            'roles.view' => [
                'en' => 'Allows viewing RBAC role definitions.',
                'uk' => 'Дозволяє переглядати визначення ролей RBAC.',
                'de' => 'Erlaubt das Anzeigen von RBAC-Rollendefinitionen.',
            ],
            'roles.create' => [
                'en' => 'Allows creating new RBAC roles.',
                'uk' => 'Дозволяє створювати нові ролі RBAC.',
                'de' => 'Erlaubt das Erstellen neuer RBAC-Rollen.',
            ],
            'roles.edit' => [
                'en' => 'Allows editing role metadata and permission mapping.',
                'uk' => 'Дозволяє редагувати метадані ролей і зв’язки дозволів.',
                'de' => 'Erlaubt das Bearbeiten von Rollenmetadaten und Berechtigungszuordnungen.',
            ],
            'roles.delete' => [
                'en' => 'Allows deleting RBAC roles when safe constraints permit.',
                'uk' => 'Дозволяє видаляти ролі RBAC, коли це безпечно за обмеженнями.',
                'de' => 'Erlaubt das Löschen von RBAC-Rollen, sofern sichere Einschränkungen erfüllt sind.',
            ],
            'permissions.view' => [
                'en' => 'Allows viewing permission catalog entries.',
                'uk' => 'Дозволяє переглядати елементи каталогу дозволів.',
                'de' => 'Erlaubt das Anzeigen von Einträgen im Berechtigungskatalog.',
            ],
            'permissions.create' => [
                'en' => 'Allows creating new permission definitions.',
                'uk' => 'Дозволяє створювати нові визначення дозволів.',
                'de' => 'Erlaubt das Erstellen neuer Berechtigungsdefinitionen.',
            ],
            'permissions.edit' => [
                'en' => 'Allows editing existing permission definitions.',
                'uk' => 'Дозволяє редагувати існуючі визначення дозволів.',
                'de' => 'Erlaubt das Bearbeiten bestehender Berechtigungsdefinitionen.',
            ],
            'permissions.delete' => [
                'en' => 'Allows deleting permission definitions.',
                'uk' => 'Дозволяє видаляти визначення дозволів.',
                'de' => 'Erlaubt das Löschen von Berechtigungsdefinitionen.',
            ],
            'tokens.view' => [
                'en' => 'Allows viewing API token inventory.',
                'uk' => 'Дозволяє переглядати реєстр API-токенів.',
                'de' => 'Erlaubt das Anzeigen des API-Token-Bestands.',
            ],
            'tokens.create' => [
                'en' => 'Allows creating new API tokens.',
                'uk' => 'Дозволяє створювати нові API-токени.',
                'de' => 'Erlaubt das Erstellen neuer API-Token.',
            ],
            'tokens.delete' => [
                'en' => 'Allows deleting existing API tokens.',
                'uk' => 'Дозволяє видаляти існуючі API-токени.',
                'de' => 'Erlaubt das Löschen vorhandener API-Token.',
            ],
        ];

        $rows = [];

        foreach ($labels as $key => $translations) {
            foreach ($translations as $locale => $value) {
                $rows[] = [
                    'locale' => $locale,
                    'group' => 'permissions',
                    'key' => $key,
                    'value' => $value,
                ];
            }
        }

        foreach ($descriptions as $key => $translations) {
            foreach ($translations as $locale => $value) {
                $rows[] = [
                    'locale' => $locale,
                    'group' => 'permission_descriptions',
                    'key' => $key,
                    'value' => $value,
                ];
            }
        }

        $this->seedTranslations($rows);
    }
}
