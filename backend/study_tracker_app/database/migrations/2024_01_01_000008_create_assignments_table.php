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
        Schema::create('assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('course_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('due_date');
            $table->string('priority', 20)->default('medium');
            $table->string('status', 20)->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->boolean('reminder_sent')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            
            $table->index('user_id');
            $table->index('course_id');
            $table->index('due_date');
            $table->index('status');
            $table->index(['user_id', 'status']);
        });

        // Add check constraints for PostgreSQL
        if (config('database.default') === 'pgsql') {
            DB::statement("ALTER TABLE assignments ADD CONSTRAINT chk_assignment_priority CHECK (priority IN ('high', 'medium', 'low'))");
            DB::statement("ALTER TABLE assignments ADD CONSTRAINT chk_assignment_status CHECK (status IN ('pending', 'in-progress', 'completed', 'overdue'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};

