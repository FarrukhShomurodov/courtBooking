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
        'stadium_id'
    ];

    public function stadium(): BelongsTo
    {
        return $this->belongsTo(Stadium::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(Day::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
