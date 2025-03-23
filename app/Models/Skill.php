<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skill extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
    ];

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
