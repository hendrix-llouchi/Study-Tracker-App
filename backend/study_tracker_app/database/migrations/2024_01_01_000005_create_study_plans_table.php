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
        Schema::create('study_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('course_id');
            $table->string('topic');
            $table->text('description')->nullable();
            $table->date('date');
            $table->time('start_time');
            $table->integer('planned_duration');
            $table->integer('actual_duration')->nullable();
            $table->string('priority', 20)->default('medium');
            $table->string('study_type', 50)->default('review');
            $table->string('status', 20)->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            
            $table->index('user_id');
            $table->index('course_id');
            $table->index('date');
            $table->index('status');
            $table->index(['user_id', 'date']);
            $table->index('priority');
        });

        // Add check constraints for PostgreSQL
        if (config('database.default') === 'pgsql') {
            DB::statement("ALTER TABLE study_plans ADD CONSTRAINT chk_priority_valid CHECK (priority IN ('high', 'medium', 'low'))");
            DB::statement("ALTER TABLE study_plans ADD CONSTRAINT chk_study_type_valid CHECK (study_type IN ('review', 'new-material', 'practice'))");
            DB::statement("ALTER TABLE study_plans ADD CONSTRAINT chk_status_valid CHECK (status IN ('pending', 'in-progress', 'completed', 'missed'))");
            DB::statement('ALTER TABLE study_plans ADD CONSTRAINT chk_planned_duration_positive CHECK (planned_duration > 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_plans');
    }
};

