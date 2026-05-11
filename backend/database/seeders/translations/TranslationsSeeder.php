<?php

namespace Database\Seeders\translations;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TranslationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            RoleTranslationsSeeder::class,
            PermissionTranslationsSeeder::class,
            SettingsTranslationsSeeder::class,
            UserTranslationsSeeder::class,
            TokenTranslationsSeeder::class,
            ActivityTranslationsSeeder::class,
            DashboardTranslationsSeeder::class,
            AuthTranslationsSeeder::class,
            ValidationTranslationsSeeder::class,
            NotificationTranslationsSeeder::class,
        ]);
    }
}
