<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_chat_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('session_id');
            $table->string('role', 20);
            $table->text('message');
            $table->json('context_data')->nullable();
            $table->integer('tokens_used')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->index('user_id');
            $table->index('session_id');
            $table->index('created_at');
        });

        // Add check constraint for PostgreSQL
        if (config('database.default') === 'pgsql') {
            DB::statement("ALTER TABLE ai_chat_history ADD CONSTRAINT chk_role_valid CHECK (role IN ('user', 'assistant'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_chat_history');
    }
};

