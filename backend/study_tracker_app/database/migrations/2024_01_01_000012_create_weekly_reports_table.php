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
        Schema::create('weekly_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->date('week_start_date');
            $table->date('week_end_date');
            $table->decimal('total_study_hours', 5, 2)->default(0.00);
            $table->decimal('planned_hours', 5, 2)->default(0.00);
            $table->decimal('completion_rate', 5, 2)->default(0.00);
            $table->uuid('most_studied_course_id')->nullable();
            $table->uuid('least_studied_course_id')->nullable();
            $table->string('performance_trend', 20)->nullable();
            $table->text('ai_insights')->nullable();
            $table->json('report_data')->nullable();
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('most_studied_course_id')->references('id')->on('courses')->onDelete('set null');
            $table->foreign('least_studied_course_id')->references('id')->on('courses')->onDelete('set null');
            
            $table->unique(['user_id', 'week_start_date']);
            $table->index('user_id');
            $table->index('week_start_date');
            $table->index('generated_at');
        });

        // Add check constraint for PostgreSQL
        if (config('database.default') === 'pgsql') {
            DB::statement("ALTER TABLE weekly_reports ADD CONSTRAINT chk_performance_trend CHECK (performance_trend IN ('improving', 'declining', 'stable'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_reports');
    }
};

