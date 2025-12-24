<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudySession extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'study_plan_id',
        'course_id',
        'start_time',
        'end_time',
        'duration',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'duration' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function studyPlan(): BelongsTo
    {
        return $this->belongsTo(StudyPlan::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Calculate duration if not set (for MySQL compatibility).
     */
    protected static function booted(): void
    {
        static::saving(function ($session) {
            if ($session->start_time && $session->end_time && !$session->duration) {
                $session->duration = $session->start_time->diffInMinutes($session->end_time);
            }
        });
    }
}

