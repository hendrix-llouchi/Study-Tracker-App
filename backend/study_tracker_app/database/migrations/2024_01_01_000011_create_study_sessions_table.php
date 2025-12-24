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
        Schema::create('study_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('study_plan_id')->nullable();
            $table->uuid('course_id')->nullable();
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            // Duration will be generated column for PostgreSQL, nullable for MySQL
            if (config('database.default') !== 'pgsql') {
                $table->integer('duration')->nullable();
            }
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('study_plan_id')->references('id')->on('study_plans')->onDelete('set null');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('set null');
            
            $table->index('user_id');
            $table->index('study_plan_id');
            $table->index('start_time');
        });

        // For PostgreSQL, add generated column and constraint
        if (config('database.default') === 'pgsql') {
            DB::statement('ALTER TABLE study_sessions ADD COLUMN duration INTEGER GENERATED ALWAYS AS (EXTRACT(EPOCH FROM (end_time - start_time)) / 60) STORED');
            DB::statement('ALTER TABLE study_sessions ADD CONSTRAINT chk_session_time_valid CHECK (end_time IS NULL OR end_time > start_time)');
        } elseif (config('database.default') === 'mysql') {
            // For MySQL, add check constraint (MySQL 8.0+)
            DB::statement('ALTER TABLE study_sessions ADD CONSTRAINT chk_session_time_valid CHECK (end_time IS NULL OR end_time > start_time)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_sessions');
    }
};

