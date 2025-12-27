<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasUuids, Notifiable, SoftDeletes;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
        'university',
        'semester',
        'onboarding_completed',
        'onboarding_step',
        'google_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'onboarding_completed' => 'boolean',
        ];
    }

    /**
     * Get the user's preferences.
     */
    public function preferences(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    /**
     * Get the user's courses.
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    /**
     * Get the user's academic results.
     */
    public function academicResults(): HasMany
    {
        return $this->hasMany(AcademicResult::class);
    }

    /**
     * Get the user's study plans.
     */
    public function studyPlans(): HasMany
    {
        return $this->hasMany(StudyPlan::class);
    }

    /**
     * Get the user's timetables.
     */
    public function timetables(): HasMany
    {
        return $this->hasMany(Timetable::class);
    }

    /**
     * Get the user's assignments.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * Get the user's notifications.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the user's email logs.
     */
    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }

    /**
     * Get the user's study sessions.
     */
    public function studySessions(): HasMany
    {
        return $this->hasMany(StudySession::class);
    }

    /**
     * Get the user's weekly reports.
     */
    public function weeklyReports(): HasMany
    {
        return $this->hasMany(WeeklyReport::class);
    }

    /**
     * Get the user's AI chat history.
     */
    public function aiChatHistory(): HasMany
    {
        return $this->hasMany(AiChatHistory::class);
    }

    /**
     * Get the user's file uploads.
     */
    public function fileUploads(): HasMany
    {
        return $this->hasMany(FileUpload::class);
    }

    /**
     * Get the user's activity logs.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}

