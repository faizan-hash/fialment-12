<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Get the users that belong to the team.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Get the admin who created the team.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the students in this team.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->wherePivot('role', User::ROLE_STUDENT);
    }

    /**
     * Get the subject mentors in this team.
     */
    public function subjectMentors(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->wherePivot('role', User::ROLE_SUBJECT_MENTOR);
    }

    /**
     * Get the personal coaches in this team.
     */
    public function personalCoaches(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->wherePivot('role', User::ROLE_PERSONAL_COACH);
    }

    /**
     * Get all feedback for this team.
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * Get the coach-student relationships in this team.
     */
    public function coachStudentRelationships()
    {
        return $this->hasMany(CoachStudent::class);
    }

    /**
     * Get the invitations for this team.
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class);
    }
}
