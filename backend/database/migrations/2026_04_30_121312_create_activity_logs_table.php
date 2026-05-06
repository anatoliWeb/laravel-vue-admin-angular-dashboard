<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create activity logs table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            /**
             * User who triggered the event
             */
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            /**
             * Action key (create_token, update_role, etc.)
             */
            $table->string('action');

            /**
             * Optional description
             */
            $table->text('description')->nullable();

            /**
             * Extra data (JSON)
             */
            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
