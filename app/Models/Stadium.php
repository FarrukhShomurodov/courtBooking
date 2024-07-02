<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stadium extends Model
{
    use HasFactory;

    protected $table = 'stadiums';

    protected $fillable = [
        'name',
        'description',
        'address',
        'map_link',
        'photos',
        'coach_id',
        'owner_id',
    ];

    public function courts(): HasMany
    {
        return $this->hasMany(Court::class);
    }

    public function sportTypes(): BelongsToMany
    {
        return $this->belongsToMany(SportType::class, 'sport_stadium');
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
