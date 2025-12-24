<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyReport extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'week_start_date',
        'week_end_date',
        'total_study_hours',
        'planned_hours',
        'completion_rate',
        'most_studied_course_id',
        'least_studied_course_id',
        'performance_trend',
        'ai_insights',
        'report_data',
        'generated_at',
        'email_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'week_start_date' => 'date',
            'week_end_date' => 'date',
            'total_study_hours' => 'decimal:2',
            'planned_hours' => 'decimal:2',
            'completion_rate' => 'decimal:2',
            'generated_at' => 'datetime',
            'email_sent_at' => 'datetime',
            'report_data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mostStudiedCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'most_studied_course_id');
    }

    public function leastStudiedCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'least_studied_course_id');
    }
}

