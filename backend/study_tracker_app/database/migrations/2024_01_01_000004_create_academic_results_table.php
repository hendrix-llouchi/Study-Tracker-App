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
        Schema::create('academic_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('course_id');
            $table->string('assessment_type', 100);
            $table->string('assessment_name')->nullable();
            $table->decimal('score', 5, 2);
            $table->decimal('max_score', 5, 2)->default(100.00);
            // Percentage will be generated column for PostgreSQL, nullable for MySQL
            if (config('database.default') !== 'pgsql') {
                $table->decimal('percentage', 5, 2)->nullable();
            }
            $table->string('grade', 5)->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->string('semester', 100);
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            
            $table->index('user_id');
            $table->index('course_id');
            $table->index('date');
            $table->index('semester');
            $table->index(['user_id', 'course_id']);
        });

        // For PostgreSQL, add generated column and constraints
        if (config('database.default') === 'pgsql') {
            DB::statement('ALTER TABLE academic_results ADD COLUMN percentage DECIMAL(5,2) GENERATED ALWAYS AS ((score / max_score) * 100) STORED');
            DB::statement('ALTER TABLE academic_results ADD CONSTRAINT chk_score_positive CHECK (score >= 0)');
            DB::statement('ALTER TABLE academic_results ADD CONSTRAINT chk_max_score_positive CHECK (max_score > 0)');
            DB::statement('ALTER TABLE academic_results ADD CONSTRAINT chk_score_not_exceed_max CHECK (score <= max_score)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_results');
    }
};

