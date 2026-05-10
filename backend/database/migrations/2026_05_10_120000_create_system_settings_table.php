<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('scope_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('scope_role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->foreignId('scope_permission_id')->nullable()->constrained('permissions')->nullOnDelete();
            $table->string('key', 160);
            $table->string('label', 160);
            $table->string('group', 100)->default('general');
            $table->text('description')->nullable();
            $table->string('type', 50)->default('string');
            $table->text('value')->nullable();
            $table->text('default_value')->nullable();
            $table->boolean('is_frontend')->default(true);
            $table->boolean('is_backend')->default(true);
            $table->unsignedInteger('priority')->default(100);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['key', 'is_active']);
            $table->index(['group', 'is_active']);
            $table->index(['scope_user_id', 'scope_role_id', 'scope_permission_id'], 'settings_scope_idx');
            $table->unique(['key', 'scope_user_id', 'scope_role_id', 'scope_permission_id'], 'settings_scope_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};

