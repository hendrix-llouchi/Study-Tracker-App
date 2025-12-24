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
        Schema::create('timetable_classes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('timetable_id');
            $table->uuid('course_id');
            $table->integer('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('location')->nullable();
            $table->string('class_type', 50)->default('lecture');
            $table->string('instructor')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('timetable_id')->references('id')->on('timetables')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            
            $table->index('timetable_id');
            $table->index('course_id');
            $table->index('day_of_week');
        });

        // Add check constraints for PostgreSQL
        if (config('database.default') === 'pgsql') {
            DB::statement('ALTER TABLE timetable_classes ADD CONSTRAINT chk_day_valid CHECK (day_of_week BETWEEN 0 AND 6)');
            DB::statement('ALTER TABLE timetable_classes ADD CONSTRAINT chk_time_valid CHECK (end_time > start_time)');
            DB::statement("ALTER TABLE timetable_classes ADD CONSTRAINT chk_class_type_valid CHECK (class_type IN ('lecture', 'lab', 'tutorial', 'seminar'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_classes');
    }
};

