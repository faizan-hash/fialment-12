<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamInvitation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'team_id',
        'invited_by',
        'email',
        'role',
        'token',
        'expires_at',
        'accepted_at',
        'rejected_at',
        'custom_message',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Additional attributes that are not stored in the database.
     *
     * @var array<int, string>
     */
    public $appends = ['skip_observer_email'];

    /**
     * Flag to control whether the observer should send an email.
     * Default is false (allow observer to send email).
     *
     * @var bool
     */
    protected $skipObserverEmail = false;

    /**
     * Get the skip_observer_email flag.
     */
    public function getSkipObserverEmailAttribute(): bool
    {
        return $this->skipObserverEmail;
    }

    /**
     * Set the skip_observer_email flag.
     */
    public function setSkipObserverEmail(bool $value): self
    {
        $this->skipObserverEmail = $value;
        return $this;
    }

    /**
     * Get the team that owns the invitation.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the user who sent the invitation.
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Determine if the invitation has been accepted.
     */
    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    /**
     * Determine if the invitation has been rejected.
     */
    public function isRejected(): bool
    {
        return $this->rejected_at !== null;
    }

    /**
     * Determine if the invitation has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Determine if the invitation is pending.
     */
    public function isPending(): bool
    {
        return !$this->isAccepted() && !$this->isRejected() && !$this->isExpired();
    }
}
