<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicResult extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'course_id',
        'assessment_type',
        'assessment_name',
        'score',
        'max_score',
        'percentage',
        'grade',
        'weight',
        'semester',
        'date',
        'notes',
        'file_path',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'max_score' => 'decimal:2',
            'percentage' => 'decimal:2',
            'weight' => 'decimal:2',
            'date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Calculate percentage if not set (for MySQL compatibility).
     */
    protected static function booted(): void
    {
        static::saving(function ($result) {
            if ($result->score && $result->max_score && !$result->percentage) {
                $result->percentage = ($result->score / $result->max_score) * 100;
            }
        });
    }
}

