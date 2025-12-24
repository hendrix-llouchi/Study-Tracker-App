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
        Schema::create('email_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->string('email_type', 50);
            $table->string('recipient_email', 255);
            $table->string('subject', 500);
            $table->string('status', 20)->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            $table->index('user_id');
            $table->index('status');
            $table->index('email_type');
            $table->index('created_at');
        });

        // Add check constraints for PostgreSQL
        if (config('database.default') === 'pgsql') {
            DB::statement("ALTER TABLE email_logs ADD CONSTRAINT chk_email_status CHECK (status IN ('pending', 'sent', 'failed', 'bounced'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};

