<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Court extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'photos',
        'is_active',
        'stadium_id',
        'sport_type_id'
    ];

    public function stadium(): BelongsTo
    {
        return $this->belongsTo(Stadium::class);
    }

    public function sportTypes(): BelongsTo
    {
        return $this->belongsTo(SportType::class, 'sport_type_id', 'id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function getMinimumCost(): ?int
    {
        return $this->schedules()->min('cost');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
