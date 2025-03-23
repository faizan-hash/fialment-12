<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Skill extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'skill_area_id',
    ];

    /**
     * Get the skill area this skill belongs to.
     */
    public function skillArea(): BelongsTo
    {
        return $this->belongsTo(SkillArea::class);
    }

    /**
     * Get the practices for this skill.
     */
    public function practices(): HasMany
    {
        return $this->hasMany(Practice::class)->orderBy('order');
    }

    /**
     * Get the feedback related to this skill.
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }
}
