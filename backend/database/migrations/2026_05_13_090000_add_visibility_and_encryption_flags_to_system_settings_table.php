<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table): void {
            $table->boolean('is_public')
                ->default(false)
                ->after('is_backend')
                ->comment('Safe for public frontend bootstrap if needed.');

            $table->boolean('is_encrypted')
                ->default(false)
                ->after('is_public')
                ->comment('Reserved for encrypted-at-rest setting values.');

            $table->index(['is_frontend', 'is_active'], 'settings_frontend_active_idx');
            $table->index(['is_backend', 'is_active'], 'settings_backend_active_idx');
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table): void {
            $table->dropIndex('settings_frontend_active_idx');
            $table->dropIndex('settings_backend_active_idx');
            $table->dropColumn(['is_public', 'is_encrypted']);
        });
    }
};

