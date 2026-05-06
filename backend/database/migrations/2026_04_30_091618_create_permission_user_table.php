<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pivot: users ↔ permissions (direct permissions)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_user', function (Blueprint $table) {

            /**
             * User reference
             */
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            /**
             * Permission reference
             */
            $table->foreignId('permission_id')
                ->constrained()
                ->cascadeOnDelete();

            /**
             * Composite primary key
             */
            $table->primary(['user_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_user');
    }
};