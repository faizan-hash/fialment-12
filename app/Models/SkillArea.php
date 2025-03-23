<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SkillArea extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'color',
    ];

    /**
     * Get the skills that belong to this area.
     */
    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class);
    }
} 