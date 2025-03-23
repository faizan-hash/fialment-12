<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'team_id',
        'skill_id',
        'practice_id',
        'comments',
        'is_positive',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_positive' => 'boolean',
    ];

    /**
     * Get the user who sent the feedback.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the user who received the feedback.
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get the team this feedback belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the skill this feedback is about.
     */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    /**
     * Get the practice this feedback is about.
     */
    public function practice(): BelongsTo
    {
        return $this->belongsTo(Practice::class);
    }

    /**
     * Alias for the recipient relation for backward compatibility.
     */
    public function receiver(): BelongsTo
    {
        return $this->recipient();
    }
}
