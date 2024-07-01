<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
}
