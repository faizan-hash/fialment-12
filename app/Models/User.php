<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    // Role constants
    const ROLE_ADMIN = 'admin';
    const ROLE_STUDENT = 'student';
    const ROLE_SUBJECT_MENTOR = 'subject_mentor';
    const ROLE_PERSONAL_COACH = 'personal_coach';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
        ];
    }

    /**
     * Get the teams the user belongs to.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class);
    }

    /**
     * Get teams created by this user (as admin).
     */
    public function createdTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'created_by');
    }

    /**
     * Get students coached by this user (if user is a coach).
     */
    public function coachingStudents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'coach_student', 'coach_id', 'student_id')
            ->withPivot('team_id')
            ->withTimestamps();
    }

    /**
     * Get the personal coach of this user (if user is a student).
     */
    public function personalCoaches(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'coach_student', 'student_id', 'coach_id')
            ->withPivot('team_id')
            ->withTimestamps();
    }

    /**
     * Get feedback sent by this user.
     */
    public function sentFeedback(): HasMany
    {
        return $this->hasMany(Feedback::class, 'sender_id');
    }

    /**
     * Get feedback received by this user.
     */
    public function receivedFeedback(): HasMany
    {
        return $this->hasMany(Feedback::class, 'receiver_id');
    }

    /**
     * Get invitations sent by this user.
     */
    public function sentInvitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class, 'invited_by');
    }

    /**
     * Check if user has an admin role.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is a student.
     */
    public function isStudent(): bool
    {
        return $this->hasRole('student');
    }

    /**
     * Check if user is a subject mentor.
     */
    public function isSubjectMentor(): bool
    {
        return $this->hasRole('subject_mentor');
    }

    /**
     * Check if user is a personal coach.
     */
    public function isPersonalCoach(): bool
    {
        return $this->hasRole('personal_coach');
    }
}
