<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SportType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'photos'
    ];

    public function stadiums(): BelongsToMany
    {
        return $this->belongsToMany(Stadium::class, 'sport_stadium');
    }
    public function courts(): HasMany
    {
        return $this->hasMany(Court::class);
    }

    public function coaches(): BelongsToMany
    {
        return $this->belongsToMany(Coach::class, 'coach_sport_type', 'sport_type_id', 'coach_id');
    }
}
